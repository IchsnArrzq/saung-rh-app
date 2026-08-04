<?php

namespace App\Services\Customer;

use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    /**
     * @return array{upcomingReservations: \Illuminate\Database\Eloquent\Collection<int, Reservation>, reservationHistory: \Illuminate\Database\Eloquent\Collection<int, Reservation>, stats: array{active_booking:int,total_booking:int,total_item_upcoming:int}}
     */
    public function data(): array
    {
        $customerId = Auth::id();

        $upcomingReservations = Reservation::query()
            ->with(['table', 'items'])
            ->where('user_id', $customerId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('reservation_at', '>=', now())
            ->orderBy('reservation_at')
            ->limit(5)
            ->get();

        $reservationHistory = Reservation::query()
            ->with(['table', 'items'])
            ->where('user_id', $customerId)
            ->latest('reservation_at')
            ->limit(10)
            ->get();

        return [
            'upcomingReservations' => $upcomingReservations,
            'reservationHistory' => $reservationHistory,
            'stats' => [
                'active_booking' => $upcomingReservations->count(),
                'total_booking' => $reservationHistory->count(),
                'total_item_upcoming' => $upcomingReservations->sum(fn ($reservation) => $reservation->items->sum('qty')),
            ],
        ];
    }
}
