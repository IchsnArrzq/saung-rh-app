<?php

namespace App\Services\Chat;

interface ChatServiceInterface
{
    /**
     * Whether the chat backend (Redis) is reachable.
     */
    public function available(): bool;

    /**
     * Recent messages for a table's own room (people seated together), oldest
     * first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function messages(string $tableId): array;

    /**
     * Post a message to a table's own private room.
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException when the body is empty or the backend is down.
     */
    public function post(string $tableId, string $tableCode, string $body, string $senderId, ?string $senderName = null): array;

    /**
     * The most recent message of a table's own room, for list previews.
     *
     * @return array<string, mixed>|null
     */
    public function roomLastMessage(string $tableId): ?array;

    /**
     * Recent messages of a table-to-table direct conversation, oldest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function dmMessages(string $tableIdA, string $tableIdB): array;

    /**
     * Post a message from one table to another (WhatsApp-style DM).
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException when the body is empty or the backend is down.
     */
    public function postDm(string $fromTableId, string $fromTableCode, string $toTableId, string $toTableCode, string $body, string $senderId, ?string $senderName = null): array;

    /**
     * The most recent message of a table-to-table conversation, for previews.
     *
     * @return array<string, mixed>|null
     */
    public function dmLastMessage(string $tableIdA, string $tableIdB): ?array;

    /**
     * Wipe a table's own room (used by tests / demos).
     */
    public function flush(string $tableId): void;
}
