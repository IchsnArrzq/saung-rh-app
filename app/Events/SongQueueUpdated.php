<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Papan DJ (Antrean lagu) mendengarkan kanal publik `songs`. Siaran langsung,
 * bukan lewat antrean — lihat App\Support\LiveUpdate.
 */
class SongQueueUpdated implements ShouldBroadcastNow
{
    use Dispatchable;

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('songs')];
    }
}
