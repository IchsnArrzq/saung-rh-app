<?php

namespace App\Domains\Table\Listeners;

use App\Domains\Order\Events\OrderPlaced;
use App\Domains\Table\Actions\ClaimTableForOrderAction;
use App\Domains\Table\Repositories\TableRepository;

/**
 * The floor's reaction to an order landing: move the table to "order_in".
 *
 * Lives in the Table domain because deciding what a table status may become is
 * the Table domain's business. Order announces the fact; this decides what it
 * means for the floor.
 */
class ClaimTableOnOrderPlaced
{
    public function __construct(
        private readonly TableRepository $tables,
        private readonly ClaimTableForOrderAction $claimTable,
    ) {}

    public function handle(OrderPlaced $event): void
    {
        if (! $event->tableId) {
            return;
        }

        $table = $this->tables->find($event->tableId);

        if (! $table) {
            return;
        }

        $this->claimTable->handle($table, $event->source->claimsOccupiedTable());
    }
}
