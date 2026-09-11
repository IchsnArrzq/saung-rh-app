<?php

namespace App\Domains\Order\DTO;

/**
 * Payload for a dine-in ticket a guest sends from the QR menu. No payment
 * fields: a guest ticket is always settled later by the cashier.
 *
 * Carries the guest's table *session*, never a table id: the table is whatever
 * the staff-approved session belongs to, so a guest cannot aim an order at a
 * table they are not sitting at.
 */
final readonly class PlaceGuestOrderData
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(
        public array $items,
        public string $tableSessionId,
        public ?string $customerName = null,
        public ?string $notes = null,
    ) {}
}
