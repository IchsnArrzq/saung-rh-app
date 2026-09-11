<?php

namespace App\Domains\Table\UseCases;

use App\Domains\Table\Enums\TableSessionCloseReason;
use App\Domains\Table\Enums\TableStatus;
use App\Domains\Table\Repositories\TableRepository;
use App\Domains\Table\Repositories\TableSessionRepository;
use Illuminate\Support\Facades\DB;
use App\Models\Table;

/**
 * Turns a table over once the party has paid and left: it moves to "cleaning"
 * so the OB crew can prep it, and its QR session is closed (ending ordering,
 * chat, song and special-request access for every phone at the table).
 *
 * Replaces App\Services\Tables\TableTurnoverService. The old code looked the
 * status row up by key with an `?? available` fallback in case the row was
 * missing — impossible now the states are an Enum, so the fallback is gone.
 */
class ReleaseTableUseCase
{
    public function __construct(
        private readonly TableRepository $tables,
        private readonly TableSessionRepository $sessions,
    ) {}

    public function handle(Table $table): void
    {
        DB::transaction(function () use ($table): void {
            if ($table->status !== TableStatus::Cleaning->value) {
                $this->tables->update($table, ['status' => TableStatus::Cleaning->value]);
            }

            $this->sessions->closeLiveForTable($table->id, TableSessionCloseReason::BillsCleared);
        });
    }
}
