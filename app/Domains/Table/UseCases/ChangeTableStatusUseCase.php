<?php

namespace App\Domains\Table\UseCases;

use App\Domains\Table\Enums\TableSessionCloseReason;
use App\Domains\Table\Enums\TableStatus;
use App\Domains\Table\Repositories\TableRepository;
use App\Domains\Table\Repositories\TableSessionRepository;
use App\Events\TableSessionsChanged;
use App\Models\Table;
use Illuminate\Support\Facades\DB;

/**
 * Sets a table's status. A floor status has no transition rules to enforce
 * (see the note on TableStatus), so the value of this UseCase is having exactly
 * one place that writes the column.
 *
 * One consequence it does own: a table staff mark free, or hand to the OB crew,
 * has nobody seated at it — so whatever QR session still holds it ends here.
 * Without this a party that left without ordering kept its session forever,
 * and the next party to scan that table was dropped into it.
 */
class ChangeTableStatusUseCase
{
    public function __construct(
        private readonly TableRepository $tables,
        private readonly TableSessionRepository $sessions,
    ) {}

    public function handle(Table $table, TableStatus $status): Table
    {
        if ($table->status === $status->value) {
            return $table;
        }

        $closed = 0;

        $table = DB::transaction(function () use ($table, $status, &$closed): Table {
            $this->tables->update($table, ['status' => $status->value]);

            if (in_array($status, [TableStatus::Available, TableStatus::Cleaning], true)) {
                $closed = $this->sessions->closeLiveForTable($table->id, TableSessionCloseReason::TableReleased);
            }

            return $table;
        });

        if ($closed > 0) {
            DB::afterCommit(fn () => rescue(fn () => TableSessionsChanged::dispatch()));
        }

        return $table;
    }

    /** Convenience for callers that only hold an id. */
    public function byId(string $tableId, TableStatus $status): ?Table
    {
        $table = $this->tables->find($tableId);

        return $table ? $this->handle($table, $status) : null;
    }
}
