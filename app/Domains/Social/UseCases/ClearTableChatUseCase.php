<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Social\Services\ChatService;
use App\Domains\Table\Repositories\TableRepository;
use App\Events\ChatMessagePosted;
use App\Events\FloorActivity;
use App\Models\Table;
use App\Support\LiveUpdate;
use RuntimeException;

/**
 * "Bersihkan obrolan" di Panel meja: hapus ruang obrolan meja ini beserta
 * obrolan antar-meja yang melibatkannya — biasanya saat rombongan pergi, supaya
 * tamu berikutnya tidak membaca percakapan orang lain.
 */
class ClearTableChatUseCase
{
    public function __construct(
        private readonly ChatService $chat,
        private readonly TableRepository $tables,
    ) {}

    /**
     * @throws RuntimeException when Redis is unreachable.
     */
    public function handle(Table $table): void
    {
        $tableId = (string) $table->id;

        $allTableIds = $this->tables->allOrdered()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $this->chat->flushTable($tableId, $allTableIds);

        // Setiap meja yang mungkin punya DM dengan meja ini menyegarkan obrolannya.
        LiveUpdate::send(new ChatMessagePosted($allTableIds, ['cleared' => true, 'table_id' => $tableId]));
        LiveUpdate::send(new FloorActivity($tableId, FloorActivity::CHAT, FloorActivity::UPDATED, $table->code));
    }
}
