<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Social\Services\ChatService;

/**
 * Staf membuka tab Obrolan satu meja — titik "pesan baru" di kartunya padam
 * sampai tamu menulis lagi. Dicatat per staf: pelayan lain tetap melihat
 * titiknya.
 */
class MarkTableChatSeenUseCase
{
    public function __construct(private readonly ChatService $chat) {}

    public function handle(string $staffId, string $tableId): void
    {
        $this->chat->markRoomSeen($staffId, $tableId);
    }
}
