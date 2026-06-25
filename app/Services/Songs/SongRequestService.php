<?php

namespace App\Services\Songs;

use App\Models\SongRequest;
use App\Models\TableSession;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

class SongRequestService
{
    public function queueMax(): int
    {
        return (int) config('songs.queue_max', 2);
    }

    /**
     * Active (queued/playing) requests already held by a table session.
     */
    public function activeCountForSession(string $sessionId): int
    {
        return SongRequest::query()
            ->where('table_session_id', $sessionId)
            ->active()
            ->count();
    }

    /**
     * Submit a request, enforcing the per-table queue cap (Qmax).
     *
     * @throws RuntimeException when the table already holds the maximum.
     */
    public function request(TableSession $session, string $title, ?string $artist = null, ?string $requestedBy = null): SongRequest
    {
        if ($this->activeCountForSession($session->id) >= $this->queueMax()) {
            throw new RuntimeException('Antrean lagu meja Anda penuh (maks '.$this->queueMax().' lagu aktif).');
        }

        return SongRequest::query()->create([
            'table_session_id' => $session->id,
            'table_id' => $session->table_id,
            'table_code' => $session->table?->code,
            'title' => trim($title),
            'artist' => ($artist = trim((string) $artist)) !== '' ? $artist : null,
            'requested_by' => ($requestedBy = trim((string) $requestedBy)) !== '' ? $requestedBy : null,
            'status' => 'queued',
        ]);
    }

    /**
     * Advance a request along its lifecycle: queued → playing → done.
     */
    public function advance(SongRequest $song): SongRequest
    {
        $next = match ($song->status) {
            'queued' => 'playing',
            'playing' => 'done',
            default => $song->status,
        };

        $song->update([
            'status' => $next,
            'played_at' => $next === 'playing' ? now() : $song->played_at,
        ]);

        return $song;
    }

    public function reject(SongRequest $song): SongRequest
    {
        $song->update(['status' => 'rejected']);

        return $song;
    }

    /**
     * The live queue (queued + playing), playing first then oldest requests.
     *
     * @return Collection<int, SongRequest>
     */
    public function queue(): Collection
    {
        return SongRequest::query()
            ->active()
            ->orderByRaw("CASE WHEN status = 'playing' THEN 0 ELSE 1 END")
            ->orderBy('created_at')
            ->get();
    }
}
