<?php

namespace App\Domains\Reservation\DTO;

/**
 * Payload for a customer booking a table ahead of time, with the menu they
 * want waiting for them.
 */
final readonly class PlaceReservationData
{
    /**
     * @param  array<int, array{menu_id:string,qty:int,notes?:?string}>  $items
     */
    public function __construct(
        public string $tableId,
        public int $pax,
        public string $reservationAt,
        public array $items,
        public ?string $notes = null,
        public ?string $userId = null,
        public ?string $customerName = null,
        public ?string $phone = null,
    ) {}
}
