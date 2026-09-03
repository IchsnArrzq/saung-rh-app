<?php

namespace App\Domains\Table\UseCases;

use App\Domains\Table\Enums\TableStatus;
use App\Domains\Table\Repositories\TableRepository;
use App\Models\Table;
use Illuminate\Support\Facades\DB;

/**
 * Turns a table over once the party has paid and left: it moves to "cleaning"
 * so the OB crew can prep it, and any active QR session is closed (ending chat,
 * song and special-request access).
 *
 * Replaces App\Services\Tables\TableTurnoverService. The old code looked the
 * status row up by key with an `?? available` fallback in case the row was
 * missing — impossible now the states are an Enum, so the fallback is gone.
 */
class ReleaseTableUseCase
{
    public function __construct(private readonly TableRepository $tables) {}

    public function handle(Table $table): void
    {
        DB::transaction(function () use ($table): void {
            if ($table->status !== TableStatus::Cleaning->value) {
                $this->tables->update($table, ['status' => TableStatus::Cleaning->value]);
            }

            $this->tables->closeActiveSessions($table);
        });
    }
}
