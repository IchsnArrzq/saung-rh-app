<?php

namespace App\Services\Chat;

use App\Support\ProfanityFilter;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Ephemeral inter-table chat backed by Redis. Nothing is written to the domain
 * database: the lobby is a capped, TTL'd ring buffer; reports and blocks are
 * Redis sets that expire with it.
 */
class ChatService
{
    public function __construct()
    {
    }

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

    private function messagesKey(): string
    {
        return $this->prefix().':messages';
    }

    private function reportKey(string $messageId): string
    {
        return $this->prefix().':report:'.$messageId;
    }

    private function strikesKey(string $tableId): string
    {
        return $this->prefix().':strikes:'.$tableId;
    }

    private function blockedKey(): string
    {
        return $this->prefix().':blocked';
    }

    private function ttlSeconds(): int
    {
        return (int) config('chat.ttl_minutes', 360) * 60;
    }

    /**
     * Recent messages, oldest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function messages(): array
    {
        try {
            $raw = $this->redis()->lrange($this->messagesKey(), 0, -1);
        } catch (\Throwable $e) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($json) => json_decode((string) $json, true),
            $raw ?: [],
        )));
    }

    public function isBlocked(string $tableId): bool
    {
        try {
            return (bool) $this->redis()->sismember($this->blockedKey(), $tableId);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Post a message to the lobby. Profanity is masked before storage.
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException when the table is blocked or the body is empty.
     */
    public function post(string $tableId, string $tableCode, string $body, ?string $sender = null): array
    {
        $body = trim($body);

        if ($body === '') {
            throw new RuntimeException('Pesan kosong.');
        }

        if ($this->isBlocked($tableId)) {
            throw new RuntimeException('Meja Anda diblokir dari chat karena pelanggaran berulang.');
        }

        $body = Str::limit(ProfanityFilter::clean($body), (int) config('chat.max_length', 280), '');

        $message = [
            'id' => (string) Str::uuid(),
            'table_id' => $tableId,
            'table_code' => $tableCode,
            'sender' => $sender,
            'body' => $body,
            'at' => now()->toIso8601String(),
        ];

        $key = $this->messagesKey();

        try {
            $redis = $this->redis();
            $redis->rpush($key, json_encode($message));
            $redis->ltrim($key, -(int) config('chat.max_messages', 100), -1);
            $redis->expire($key, $this->ttlSeconds());
        } catch (\Throwable $e) {
            throw new RuntimeException('Obrolan sedang tidak tersedia. Coba lagi nanti.');
        }

        return $message;
    }

    /**
     * Report a message. Each reporting table counts once per message; once a
     * sender accrues `report_threshold` upheld reports it is auto-blocked.
     *
     * @return array{reports:int, blocked:bool}
     */
    public function report(string $messageId, string $reporterTableId): array
    {
        $message = collect($this->messages())->firstWhere('id', $messageId);

        if (! $message) {
            return ['reports' => 0, 'blocked' => false];
        }

        $senderTableId = (string) $message['table_id'];

        // A table cannot report its own message.
        if ($senderTableId === $reporterTableId) {
            return ['reports' => 0, 'blocked' => $this->isBlocked($senderTableId)];
        }

        $redis = $this->redis();
        $reportKey = $this->reportKey($messageId);

        // SADD returns 1 only when the reporter is new for this message.
        $isNew = (int) $redis->sadd($reportKey, $reporterTableId);
        $redis->expire($reportKey, $this->ttlSeconds());

        $strikes = 0;

        if ($isNew === 1) {
            $strikes = (int) $redis->incr($this->strikesKey($senderTableId));
            $redis->expire($this->strikesKey($senderTableId), $this->ttlSeconds());
        } else {
            $strikes = (int) ($redis->get($this->strikesKey($senderTableId)) ?? 0);
        }

        $blocked = false;

        if ($strikes >= (int) config('chat.report_threshold', 3)) {
            $redis->sadd($this->blockedKey(), $senderTableId);
            $redis->expire($this->blockedKey(), $this->ttlSeconds());
            $blocked = true;
        }

        return ['reports' => $strikes, 'blocked' => $blocked];
    }

    /**
     * Wipe the entire lobby (used by tests / demos).
     *
     * Deletes deterministic keys by their logical names — never via KEYS, whose
     * results carry the client prefix and would be double-prefixed on delete.
     */
    public function flush(): void
    {
        $redis = $this->redis();

        $tableIds = [];

        foreach ($this->messages() as $message) {
            $redis->del($this->reportKey((string) $message['id']));
            $tableIds[] = (string) $message['table_id'];
        }

        foreach ((array) $redis->smembers($this->blockedKey()) as $blocked) {
            $tableIds[] = (string) $blocked;
        }

        foreach (array_unique($tableIds) as $tableId) {
            $redis->del($this->strikesKey($tableId));
        }

        $redis->del($this->messagesKey());
        $redis->del($this->blockedKey());
    }
}
