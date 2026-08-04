<?php

namespace App\Services\Reservations;

use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class ReservationReleaseService
{
    /**
     * Auto-release reservations that have passed their limits and free the
     * tables they were holding.
     *
     * Two cases are handled:
     *  - Expired holds: a pending booking whose deposit window lapsed without a
     *    paid deposit is cancelled.
     *  - No-shows: a confirmed booking left past its grace window without a
     *    check-in is flagged as a no-show.
     *
     * @return array{expired_holds:int, no_shows:int, tables_released:int}
     */
    public function releaseExpired(): array
    {
        $graceMinutes = (int) config('reservations.no_show_grace_minutes', 15);

        $tablesReleased = 0;

        $expiredHolds = $this->process(
            Reservation::query()->expiredHolds()->with('table')->get(),
            'cancelled',
            'hold_expired',
            $tablesReleased,
        );

        $noShows = $this->process(
            Reservation::query()->noShowCandidates($graceMinutes)->with('table')->get(),
            'no_show',
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
     * Transition a batch of reservations to a terminal status and release the
     * tables they held.
     *
     * @param  \Illuminate\Support\Collection<int, Reservation>  $reservations
     */
    private function process($reservations, string $status, string $reason, int &$tablesReleased): int
    {
        foreach ($reservations as $reservation) {
            DB::transaction(function () use ($reservation, $status, $reason, &$tablesReleased): void {
                $heldKey = optional($reservation->table)->status;

                $reservation->forceFill([
                    'status' => $status,
                    'released_at' => now(),
                    'release_reason' => $reason,
                ])->save();

                $reservation->releaseTable();

                if ($heldKey === Reservation::RESERVED_STATUS_KEY
                    && optional($reservation->table?->fresh())->status !== Reservation::RESERVED_STATUS_KEY) {
                    $tablesReleased++;
                }
            });
        }

        return $reservations->count();
    }
}
