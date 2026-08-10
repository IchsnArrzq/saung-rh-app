<?php

namespace App\Domains\Order\DTO;

/**
 * Payload for a dine-in ticket a guest sends from the QR menu. No payment
 * fields: a guest ticket is always settled later by the cashier.
 */
final readonly class PlaceGuestOrderData
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(
        public array $items,
        public string $tableId,
        public ?string $customerName = null,
        public ?string $notes = null,
    ) {}
}
