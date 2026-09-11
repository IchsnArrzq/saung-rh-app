<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Social\Enums\SongStatus;
use App\Domains\Social\Repositories\SongRequestRepository;
use App\Events\FloorActivity;
use App\Events\SongQueueUpdated;
use App\Models\SongRequest;
use App\Support\LiveUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RejectSongUseCase
{
    public function __construct(private readonly SongRequestRepository $songs) {}

    public function handle(string $songId): SongRequest
    {
        $song = $this->songs->find($songId);

        if (! $song) {
            throw ValidationException::withMessages(['song' => 'Lagu tidak ditemukan.']);
        }

        $song = DB::transaction(fn (): SongRequest => $this->songs->update($song, [
            'status' => SongStatus::Rejected->value,
        ]));

        DB::afterCommit(function () use ($song): void {
            LiveUpdate::send(new SongQueueUpdated);
            LiveUpdate::send(new FloorActivity($song->table_id, FloorActivity::SONG, FloorActivity::UPDATED, $song->table_code));
        });

        return $song;
    }
}
