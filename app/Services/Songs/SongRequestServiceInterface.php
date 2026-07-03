<?php

namespace App\Services\Songs;

use App\Models\SongRequest;
use App\Models\TableSession;
use Illuminate\Database\Eloquent\Collection;

interface SongRequestServiceInterface
{
    public function queueMax(): int;

    /**
     * Active (queued/playing) requests already held by a table session.
     */
    public function activeCountForSession(string $sessionId): int;

    /**
     * Submit a request, enforcing the per-table queue cap (Qmax).
     *
     * @throws \RuntimeException when the table already holds the maximum.
     */
    public function request(TableSession $session, string $title, ?string $artist = null, ?string $requestedBy = null): SongRequest;

    /**
     * Advance a request along its lifecycle: queued → playing → done.
     */
    public function advance(SongRequest $song): SongRequest;

    public function reject(SongRequest $song): SongRequest;

    /**
     * The live queue (queued + playing), playing first then oldest requests.
     *
     * @return Collection<int, SongRequest>
     */
    public function queue(): Collection;
}
