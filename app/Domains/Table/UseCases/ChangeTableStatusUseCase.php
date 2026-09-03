<?php

namespace App\Domains\Table\UseCases;

use App\Domains\Table\Enums\TableStatus;
use App\Domains\Table\Repositories\TableRepository;
use App\Models\Table;

/**
 * Sets a table's status. Kept deliberately thin — a floor status has no
 * transition rules to enforce (see the note on TableStatus), so the value of
 * this UseCase is having exactly one place that writes the column.
 */
class ChangeTableStatusUseCase
{
    public function __construct(private readonly TableRepository $tables) {}

    public function handle(Table $table, TableStatus $status): Table
    {
        if ($table->status === $status->value) {
            return $table;
        }

        return $this->tables->update($table, ['status' => $status->value]);
    }

    /** Convenience for callers that only hold an id. */
    public function byId(string $tableId, TableStatus $status): ?Table
    {
        $table = $this->tables->find($tableId);

        return $table ? $this->handle($table, $status) : null;
    }
}
