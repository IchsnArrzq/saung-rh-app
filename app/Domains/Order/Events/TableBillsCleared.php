<?php

namespace App\Domains\Order\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Every bill on a table has been settled.
 *
 * Deliberately narrower than "a bill was paid": a party can run several rounds,
 * so the Order domain — which owns what is still outstanding — answers that
 * question itself and only announces the fact once the table truly owes
 * nothing. The Table domain then frees the table without needing to know
 * anything about bills.
 */
class TableBillsCleared
{
    use Dispatchable;

    public function __construct(
        public readonly string $tableId,
        public readonly string $settledOrderId,
    ) {}
}
