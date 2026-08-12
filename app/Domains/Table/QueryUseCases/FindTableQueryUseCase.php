<?php

namespace App\Domains\Table\QueryUseCases;

use App\Domains\Table\Enums\TableStatus;
use App\Domains\Table\Repositories\TableRepositoryInterface;
use App\Models\Table;

/**
 * Single-table lookups that carry a rule about what the caller may do with it.
 *
 * Replaces OrderCartService::findAvailableTable / findOrderableTable, which
 * repeated the same `TableStatus::tryFrom(...)` dance in two places.
 */
class FindTableQueryUseCase
{
    public function __construct(private readonly TableRepositoryInterface $tables) {}

    public function byId(string $tableId): ?Table
    {
        return $this->tables->find($tableId);
    }

    /** A table a seated party may keep ordering on (available, occupied, order_in). */
    public function orderable(string $tableId): ?Table
    {
        $table = $this->tables->find($tableId);

        return $table && TableStatus::tryFrom((string) $table->status)?->isOrderable()
            ? $table
            : null;
    }

    /** A free table — the only kind a customer may pick or book. */
    public function free(string $tableId): ?Table
    {
        $table = $this->tables->find($tableId);

        return $table && TableStatus::tryFrom((string) $table->status)?->isFree()
            ? $table
            : null;
    }
}
