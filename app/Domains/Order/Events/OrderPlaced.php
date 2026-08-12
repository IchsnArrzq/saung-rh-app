<?php

namespace App\Domains\Order\Events;

use App\Domains\Order\Enums\OrderSource;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An order was accepted and committed.
 *
 * How the Order domain tells the rest of the app without reaching into it: the
 * Table domain listens and advances the table (ARCHITECTURE.md § Domain
 * Dependencies).
 *
 * Carries ids rather than models so a listener always reads current state — by
 * the time it runs, the write transaction has already committed.
 *
 * Not a broadcast event: the kitchen display already has OrderCreated for that.
 * This one is internal wiring.
 */
class OrderPlaced
{
    use Dispatchable;

    public function __construct(
        public readonly string $orderId,
        public readonly ?string $tableId,
        public readonly OrderSource $source,
    ) {}
}
