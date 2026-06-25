<?php

namespace App\Console\Commands;

use App\Services\Reservations\ReservationReleaseService;
use Illuminate\Console\Command;

class ReleaseExpiredReservations extends Command
{
    protected $signature = 'reservations:release-expired';

    protected $description = 'Cancel reservations past their deposit hold or no-show grace window and release the tables they held';

    public function handle(ReservationReleaseService $service): int
    {
        $result = $service->releaseExpired();

        $this->info(sprintf(
            'Released reservations — expired holds: %d, no-shows: %d, tables freed: %d.',
            $result['expired_holds'],
            $result['no_shows'],
            $result['tables_released'],
        ));

        return self::SUCCESS;
    }
}
