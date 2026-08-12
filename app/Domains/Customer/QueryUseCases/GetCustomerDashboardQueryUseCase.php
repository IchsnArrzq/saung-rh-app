<?php

namespace App\Domains\Customer\QueryUseCases;

use App\Domains\Reservation\Repositories\ReservationRepositoryInterface;
use Illuminate\Support\Facades\Auth;

/**
 * The logged-in customer's portal home: their next bookings, their history and
 * the three counters above them.
 *
 * Reads through the Reservation repository rather than querying Reservation
 * here — the customer portal is a *view* over that domain, and duplicating the
 * query is how the two drift apart.
 *
 * @todo Fase D — reach the Reservation domain through an event/read contract
 *       instead of its repository (ARCHITECTURE.md § Domain Dependencies).
 */
class GetCustomerDashboardQueryUseCase
{
    public function __construct(private readonly ReservationRepositoryInterface $reservations) {}

    /**
     * @return array{
     *     upcomingReservations: \Illuminate\Database\Eloquent\Collection,
     *     reservationHistory: \Illuminate\Database\Eloquent\Collection,
     *     stats: array{active_booking:int,total_booking:int,total_item_upcoming:int}
     * }
     */
    public function handle(?string $customerId = null): array
    {
        $customerId ??= (string) Auth::id();

        $upcoming = $this->reservations->upcomingForUser($customerId);
        $history = $this->reservations->historyForUser($customerId);

        return [
            'upcomingReservations' => $upcoming,
            'reservationHistory' => $history,
            'stats' => [
                'active_booking' => $upcoming->count(),
                'total_booking' => $history->count(),
                'total_item_upcoming' => (int) $upcoming->sum(fn ($reservation) => $reservation->items->sum('qty')),
            ],
        ];
    }
}
