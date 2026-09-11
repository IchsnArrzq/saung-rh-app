<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Social\Services\ChatService;
use App\Events\ChatMessagePosted;
use App\Events\FloorActivity;
use App\Models\Table;
use App\Models\User;
use App\Support\LiveUpdate;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Staf membalas ke obrolan meja dari Panel meja. Pesannya ditandai `staff`
 * supaya tamu melihatnya sebagai suara restoran, bukan tamu lain.
 */
class ReplyTableChatUseCase
{
    public function __construct(private readonly ChatService $chat) {}

    /**
     * @return array<string, mixed>
     *
     * @throws RuntimeException when the message is empty or Redis is unreachable.
     */
    public function handle(Table $table, User $staff, string $body): array
    {
        $tableId = (string) $table->id;

        $message = $this->chat->postAsStaff($tableId, (string) $table->code, $body, (string) $staff->id, $this->displayName($staff));

        // Membalas berarti sudah membaca.
        $this->chat->markRoomSeen((string) $staff->id, $tableId);

        LiveUpdate::send(new ChatMessagePosted([$tableId], $message));
        LiveUpdate::send(new FloorActivity($tableId, FloorActivity::CHAT, FloorActivity::UPDATED, $table->code));

        return $message;
    }

    /** "Staf · Budi" — nama depan saja; nama lengkap staf bukan urusan tamu. */
    private function displayName(User $staff): string
    {
        return 'Staf · '.Str::limit((string) Str::of((string) $staff->name)->trim()->before(' '), 16, '');
    }
}
