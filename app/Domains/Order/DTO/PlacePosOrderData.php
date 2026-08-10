<?php

namespace App\Domains\Order\DTO;

use App\Domains\Payment\Enums\PaymentMethod;

/**
 * Payload for a counter sale rung up at the POS. Six fields with a nullable
 * enum among them, so it earns a DTO rather than a long parameter list
 * (AGENTS.md § DTO).
 */
final readonly class PlacePosOrderData
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(
        public array $items,
        public ?string $tableId = null,
        public ?string $customerName = null,
        public ?string $notes = null,
        public bool $payNow = false,
        public ?PaymentMethod $paymentMethod = null,
    ) {}
}
