<?php

namespace App\Livewire\Staff;

use App\Domains\Social\QueryUseCases\GetSongQueueQueryUseCase;
use App\Domains\Social\UseCases\AdvanceSongUseCase;
use App\Domains\Social\UseCases\RejectSongUseCase;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class SongQueueBoard extends Component
{
    #[On('echo:songs,SongQueueUpdated')]
    public function onQueueUpdated(): void
    {
        // Re-render to reflect the latest queue (wire:poll is the fallback).
    }

    public function advance(AdvanceSongUseCase $advanceSong, string $id): void
    {
        // The UseCase broadcasts SongQueueUpdated once its write commits.
        $advanceSong->handle($id);
    }

    public function reject(RejectSongUseCase $rejectSong, string $id): void
    {
        $rejectSong->handle($id);
    }

    public function render(GetSongQueueQueryUseCase $songs): View
    {
        return view('livewire.staff.song-queue-board', [
            'queue' => $songs->queue(),
            'recentDone' => $songs->recentlyFinished(),
        ]);
    }
}
