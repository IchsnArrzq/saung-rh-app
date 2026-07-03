<?php

namespace App\Services\Customer;

use App\Models\Reservation;

interface DashboardServiceInterface
{
    /**
     * @return array{upcomingReservations: \Illuminate\Database\Eloquent\Collection<int, Reservation>, reservationHistory: \Illuminate\Database\Eloquent\Collection<int, Reservation>, stats: array{active_booking:int,total_booking:int,total_item_upcoming:int}}
     */
    public function data(): array;
}
