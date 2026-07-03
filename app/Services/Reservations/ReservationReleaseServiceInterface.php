<?php

namespace App\Services\Reservations;

interface ReservationReleaseServiceInterface
{
    /**
     * Auto-release reservations that have passed their limits and free the
     * tables they were holding.
     *
     * @return array{expired_holds:int, no_shows:int, tables_released:int}
     */
    public function releaseExpired(): array;
}
