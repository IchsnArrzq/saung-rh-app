<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * "The list of guests waiting at a table changed" — a scan came in, or staff
 * approved, rejected or ended a session. Staff approval trays refresh on it.
 *
 * No payload: guest names stay off the wire and the tray re-reads its list.
 * ShouldBroadcastNow because this app runs no queue worker, so a queued
 * broadcast would never reach a browser. Dispatch it inside rescue(): a Reverb
 * outage must not undo an approval — the tray's polling fallback catches up.
 */
class TableSessionsChanged implements ShouldBroadcastNow
{
    use Dispatchable;

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('table-sessions')];
    }
}
