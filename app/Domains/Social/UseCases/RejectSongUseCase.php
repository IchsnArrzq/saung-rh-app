<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Social\Enums\SongStatus;
use App\Domains\Social\Repositories\SongRequestRepositoryInterface;
use App\Events\SongQueueUpdated;
use App\Models\SongRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RejectSongUseCase
{
    public function __construct(private readonly SongRequestRepositoryInterface $songs) {}

    public function handle(string $songId): SongRequest
    {
        $song = $this->songs->find($songId);

        if (! $song) {
            throw ValidationException::withMessages(['song' => 'Lagu tidak ditemukan.']);
        }

        $song = DB::transaction(fn (): SongRequest => $this->songs->update($song, [
            'status' => SongStatus::Rejected->value,
        ]));

        DB::afterCommit(fn () => SongQueueUpdated::dispatch());

        return $song;
    }
}
