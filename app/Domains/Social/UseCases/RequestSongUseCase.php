<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Social\Enums\SongStatus;
use App\Domains\Social\Repositories\SongRequestRepositoryInterface;
use App\Events\SongQueueUpdated;
use App\Models\SongRequest;
use App\Models\TableSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A table sends a song to the DJ queue, subject to the per-table cap so one
 * party cannot monopolise the playlist.
 */
class RequestSongUseCase
{
    public function __construct(private readonly SongRequestRepositoryInterface $songs) {}

    public function handle(TableSession $session, string $title, ?string $artist = null, ?string $requestedBy = null): SongRequest
    {
        $max = $this->queueMax();

        if ($this->songs->activeCountForSession($session->id) >= $max) {
            throw ValidationException::withMessages([
                'title' => 'Antrean lagu meja Anda penuh (maks '.$max.' lagu aktif).',
            ]);
        }

        $song = DB::transaction(fn (): SongRequest => $this->songs->create([
            'table_session_id' => $session->id,
            'table_id' => $session->table_id,
            'table_code' => $session->table?->code,
            'title' => trim($title),
            'artist' => $this->blankToNull($artist),
            'requested_by' => $this->blankToNull($requestedBy),
            'status' => SongStatus::Queued->value,
        ]));

        DB::afterCommit(fn () => SongQueueUpdated::dispatch());

        return $song;
    }

    public function queueMax(): int
    {
        return (int) config('songs.queue_max', 2);
    }

    private function blankToNull(?string $value): ?string
    {
        return trim((string) $value) !== '' ? trim((string) $value) : null;
    }
}
