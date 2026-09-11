<?php

namespace App\Domains\Social\Services;

use App\Support\ProfanityFilter;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Ephemeral table chat backed by Redis. Nothing is written to the domain
 * database. Two conversation kinds share the same capped, TTL'd ring buffers:
 *
 *  - Room: a table's own private room (the people seated together + staff).
 *  - DM:   a WhatsApp-style direct conversation between two occupied tables.
 *
 * Room messages are never shared across tables; a DM is only visible to the two
 * tables that take part in it.
 */
class ChatService
{
    public function __construct() {}

    private function redis()
    {
        return Redis::connection(config('chat.redis_connection', 'default'));
    }

    /**
     * Whether the chat backend (Redis) is reachable. When it is not, the rest of
     * the page must keep working — chat simply renders an "unavailable" state.
     */
    public function available(): bool
    {
        try {
            $this->redis()->ping();

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function prefix(): string
    {
        return (string) config('chat.key_prefix', 'chat:lobby');
    }

    private function roomKey(string $tableId): string
    {
        return $this->prefix().':room:'.$tableId.':messages';
    }

    /**
     * Canonical (order-independent) key for a pair of tables, so both sides read
     * and write the same conversation regardless of who opened it.
     */
    private function dmKey(string $tableIdA, string $tableIdB): string
    {
        $pair = [$tableIdA, $tableIdB];
        sort($pair);

        return $this->prefix().':dm:'.$pair[0].':'.$pair[1].':messages';
    }

    private function ttlSeconds(): int
    {
        return (int) config('chat.ttl_minutes', 360) * 60;
    }

    /**
     * Read a capped list from Redis, oldest first.
     *
     * @return array<int, array<string, mixed>>
     */
    private function read(string $key): array
    {
        try {
            $raw = $this->redis()->lrange($key, 0, -1);
        } catch (\Throwable $e) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($json) => json_decode((string) $json, true),
            $raw ?: [],
        )));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function lastOf(string $key): ?array
    {
        try {
            $raw = $this->redis()->lindex($key, -1);
        } catch (\Throwable $e) {
            return null;
        }

        if (! $raw) {
            return null;
        }

        $decoded = json_decode((string) $raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Append a message to a conversation ring buffer with a refreshed TTL.
     *
     * @param  array<string, mixed>  $message
     *
     * @throws RuntimeException when the backend is unreachable.
     */
    private function push(string $key, array $message): void
    {
        try {
            $redis = $this->redis();
            $redis->rpush($key, json_encode($message));
            $redis->ltrim($key, -(int) config('chat.max_messages', 100), -1);
            $redis->expire($key, $this->ttlSeconds());
        } catch (\Throwable $e) {
            throw new RuntimeException('Obrolan sedang tidak tersedia. Coba lagi nanti.');
        }
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function makeMessage(string $tableId, string $tableCode, string $body, string $senderId, ?string $senderName, array $extra = []): array
    {
        $body = trim($body);

        if ($body === '') {
            throw new RuntimeException('Pesan kosong.');
        }

        $body = Str::limit(ProfanityFilter::clean($body), (int) config('chat.max_length', 280), '');

        return array_merge([
            'id' => (string) Str::uuid(),
            'table_id' => $tableId,
            'table_code' => $tableCode,
            'sender_id' => $senderId,
            'sender_name' => $senderName !== null && trim($senderName) !== '' ? trim($senderName) : null,
            'body' => $body,
            'at' => now()->toIso8601String(),
        ], $extra);
    }

    public function messages(string $tableId): array
    {
        return $this->read($this->roomKey($tableId));
    }

    public function post(string $tableId, string $tableCode, string $body, string $senderId, ?string $senderName = null): array
    {
        $message = $this->makeMessage($tableId, $tableCode, $body, $senderId, $senderName);

        $this->push($this->roomKey($tableId), $message);

        return $message;
    }

    public function roomLastMessage(string $tableId): ?array
    {
        return $this->lastOf($this->roomKey($tableId));
    }

    public function dmMessages(string $tableIdA, string $tableIdB): array
    {
        return $this->read($this->dmKey($tableIdA, $tableIdB));
    }

    public function postDm(string $fromTableId, string $fromTableCode, string $toTableId, string $toTableCode, string $body, string $senderId, ?string $senderName = null): array
    {
        $message = $this->makeMessage($fromTableId, $fromTableCode, $body, $senderId, $senderName, [
            'to_table_id' => $toTableId,
            'to_table_code' => $toTableCode,
        ]);

        $this->push($this->dmKey($fromTableId, $toTableId), $message);

        return $message;
    }

    public function dmLastMessage(string $tableIdA, string $tableIdB): ?array
    {
        return $this->lastOf($this->dmKey($tableIdA, $tableIdB));
    }

    public function flush(string $tableId): void
    {
        $this->redis()->del($this->roomKey($tableId));
    }

    /**
     * A reply from floor staff into a table's room. Flagged `staff` so the guest
     * panel and the staff Panel meja both render it as the restaurant speaking,
     * not as another guest.
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException when the message is empty or Redis is unreachable.
     */
    public function postAsStaff(string $tableId, string $tableCode, string $body, string $staffId, string $staffName): array
    {
        $message = $this->makeMessage($tableId, $tableCode, $body, 'staff:'.$staffId, $staffName, ['staff' => true]);

        $this->push($this->roomKey($tableId), $message);

        return $message;
    }

    /**
     * Wipe a table's room and every DM it took part in — "Bersihkan obrolan" on
     * the Panel meja, typically when a party leaves. DM keys are rebuilt from
     * logical names, never taken from KEYS: predis re-applies its prefix on del,
     * so a key read back from KEYS would be double-prefixed and silently missed.
     *
     * @param  iterable<int, string>  $otherTableIds
     *
     * @throws RuntimeException when Redis is unreachable.
     */
    public function flushTable(string $tableId, iterable $otherTableIds = []): void
    {
        $keys = [$this->roomKey($tableId)];

        foreach ($otherTableIds as $otherTableId) {
            if ((string) $otherTableId !== $tableId) {
                $keys[] = $this->dmKey($tableId, (string) $otherTableId);
            }
        }

        try {
            $this->redis()->del($keys);
        } catch (\Throwable $e) {
            throw new RuntimeException('Obrolan sedang tidak tersedia. Coba lagi nanti.');
        }
    }

    private function seenKey(string $staffId): string
    {
        return $this->prefix().':seen:'.$staffId;
    }

    /**
     * One staff member has now read a table's room — the Panel meja stops
     * showing its unread dot until a guest writes again.
     */
    public function markRoomSeen(string $staffId, string $tableId): void
    {
        try {
            $redis = $this->redis();
            $redis->hset($this->seenKey($staffId), $tableId, now()->toIso8601String());
            $redis->expire($this->seenKey($staffId), $this->ttlSeconds());
        } catch (\Throwable $e) {
            // Unread dots are a convenience; without Redis there is no chat to mark.
        }
    }

    /**
     * table id => ISO time the staff member last read that room.
     *
     * @return array<string, string>
     */
    public function roomSeenMap(string $staffId): array
    {
        try {
            return (array) $this->redis()->hgetall($this->seenKey($staffId));
        } catch (\Throwable $e) {
            return [];
        }
    }
}
