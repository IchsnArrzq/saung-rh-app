<?php

namespace App\Domains\Order\DTO;

/**
 * Normalised order lines plus the money computed from them.
 *
 * @phpstan-type OrderLine array{menu_id: ?string, menu_name_snapshot: ?string, qty: int, price: float, line_total: float, notes: ?string}
 */
final readonly class OrderTotals
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(
        public array $items,
        public float $subtotal,
        public float $tax,
        public float $total,
    ) {}
}
