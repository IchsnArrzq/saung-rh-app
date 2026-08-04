<?php

namespace App\Domains\Order\DTO;

use App\Domains\Order\Enums\OrderStatus;

final readonly class UpdateOrderData
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(
        public array $items,
        public OrderStatus $status,
        public ?string $tableId = null,
        public ?string $customerName = null,
        public ?string $notes = null,
        public float $tax = 0.0,
        public ?string $orderedAt = null,
    ) {}
}
