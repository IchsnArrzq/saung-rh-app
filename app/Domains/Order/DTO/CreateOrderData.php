<?php

namespace App\Domains\Order\DTO;

use App\Domains\Order\Enums\OrderStatus;

/**
 * Payload for creating an order. Carries far more than three fields and needs
 * type-safety around status/items, so it earns a DTO (AGENTS.md § DTO).
 *
 * Format validation happens before this object exists — in the Livewire Form
 * Object or FormRequest — so the UseCase never sees a raw Request.
 */
final readonly class CreateOrderData
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
        public ?string $cashierId = null,
        public ?string $customerId = null,
    ) {}
}
