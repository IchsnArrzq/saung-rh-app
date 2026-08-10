<?php

namespace App\Domains\Table\Actions;

use App\Domains\Table\Enums\TableStatus;
use App\Domains\Table\UseCases\ChangeTableStatusUseCase;
use App\Models\Table;

/**
 * Advances a table to "order_in" when an order lands on it.
 *
 * Which starting states count depends on who took the order, and that is the
 * one thing callers vary:
 *  - a dine-in ticket may advance a table that is already `occupied`, because a
 *    seated party keeps ordering;
 *  - a counter sale claims only a genuinely free table — the cashier is not on
 *    the floor and must not overwrite what the receptionist set.
 *
 * A table that is reserved, being cleaned, or already `order_in` is never
 * touched either way.
 */
class ClaimTableForOrderAction
{
    public function __construct(private readonly ChangeTableStatusUseCase $changeStatus) {}

    public function handle(Table $table, bool $includeOccupied = true): Table
    {
        $claimable = [TableStatus::Available->value];

        if ($includeOccupied) {
            $claimable[] = TableStatus::Occupied->value;
        }

        if (! in_array($table->status, $claimable, true)) {
            return $table;
        }

        return $this->changeStatus->handle($table, TableStatus::OrderIn);
    }
}
