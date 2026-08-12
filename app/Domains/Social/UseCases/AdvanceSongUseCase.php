<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Social\Enums\SongStatus;
use App\Domains\Social\Repositories\SongRequestRepositoryInterface;
use App\Events\SongQueueUpdated;
use App\Models\SongRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Moves a request one step along the DJ board: queued → playing → done.
 * A finished request stays where it is, so a stray double-click is harmless.
 */
class AdvanceSongUseCase
{
    public function __construct(private readonly SongRequestRepositoryInterface $songs) {}

    public function handle(string $songId): SongRequest
    {
        $song = $this->songs->find($songId);

        if (! $song) {
            throw ValidationException::withMessages(['song' => 'Lagu tidak ditemukan.']);
        }

        $current = $song->status;
        $next = $current->next();

        if ($next === $current) {
            return $song;
        }

        $song = DB::transaction(fn (): SongRequest => $this->songs->update($song, [
            'status' => $next->value,
            // Stamp only the moment it starts playing; later steps keep it.
            'played_at' => $next === SongStatus::Playing ? now() : $song->played_at,
        ]));

        DB::afterCommit(fn () => SongQueueUpdated::dispatch());

        return $song;
    }
}
