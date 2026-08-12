<?php

namespace App\Domains\Order\DTO;

/**
 * Payload for a round ordered from the logged-in customer portal. Like the
 * guest ticket it carries no payment — the cashier settles the table later —
 * but it does carry who ordered it.
 */
final readonly class PlaceCustomerOrderData
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(
        public array $items,
        public string $tableId,
        public ?string $notes = null,
        public ?string $customerId = null,
        public ?string $customerName = null,
    ) {}
}
