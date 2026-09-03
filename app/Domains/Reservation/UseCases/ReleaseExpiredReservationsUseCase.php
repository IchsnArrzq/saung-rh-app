<?php

namespace App\Domains\Reservation\UseCases;

use App\Domains\Reservation\Enums\ReservationStatus;
use App\Domains\Reservation\Repositories\ReservationRepository;
use App\Domains\Table\Enums\TableStatus;
use App\Models\Reservation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The scheduled sweep that stops abandoned bookings from holding tables.
 *
 * Two cases:
 *  - Expired holds: a pending booking whose deposit window lapsed unpaid is
 *    cancelled.
 *  - No-shows: a confirmed booking left past its grace window without a
 *    check-in is flagged.
 *
 * Note that online guest bookings (`hold_until` null) are intentionally out of
 * scope — they lock no table and a receptionist confirms them by hand.
 *
 * Replaces App\Services\Reservations\ReservationReleaseService.
 */
class ReleaseExpiredReservationsUseCase
{
    public function __construct(private readonly ReservationRepository $reservations) {}

    /**
     * @return array{expired_holds:int, no_shows:int, tables_released:int}
     */
    public function handle(): array
    {
        $graceMinutes = (int) config('reservations.no_show_grace_minutes', 15);
        $tablesReleased = 0;

        $expiredHolds = $this->process(
            $this->reservations->expiredHolds(),
            ReservationStatus::Cancelled,
            'hold_expired',
            $tablesReleased,
        );

        $noShows = $this->process(
            $this->reservations->noShowCandidates($graceMinutes),
            ReservationStatus::NoShow,
            'no_show',
            $tablesReleased,
        );

        return [
            'expired_holds' => $expiredHolds,
            'no_shows' => $noShows,
            'tables_released' => $tablesReleased,
        ];
    }

    /**
     * @param  Collection<int, Reservation>  $reservations
     */
    private function process(Collection $reservations, ReservationStatus $status, string $reason, int &$tablesReleased): int
    {
        foreach ($reservations as $reservation) {
            DB::transaction(function () use ($reservation, $status, $reason, &$tablesReleased): void {
                $heldBefore = $reservation->table?->status;

                $this->reservations->update($reservation, [
                    'status' => $status->value,
                    'released_at' => now(),
                    'release_reason' => $reason,
                ]);

                $reservation->releaseTable();

                // Only count a table as freed if this booking was the thing
                // holding it — another seated party may still own the table.
                if ($heldBefore === TableStatus::Reserved->value
                    && $reservation->table?->fresh()?->status !== TableStatus::Reserved->value) {
                    $tablesReleased++;
                }
            });
        }

        return $reservations->count();
    }
}
