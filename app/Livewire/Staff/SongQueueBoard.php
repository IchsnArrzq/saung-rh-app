<?php

namespace App\Livewire\Staff;

use App\Events\SongQueueUpdated;
use App\Models\SongRequest;
use App\Services\Songs\SongRequestServiceInterface;
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

    public function advance(SongRequestServiceInterface $songs, string $id): void
    {
        $songs->advance(SongRequest::query()->findOrFail($id));
        SongQueueUpdated::dispatch();
    }

    public function reject(SongRequestServiceInterface $songs, string $id): void
    {
        $songs->reject(SongRequest::query()->findOrFail($id));
        SongQueueUpdated::dispatch();
    }

    public function render(SongRequestServiceInterface $songs): View
    {
        return view('livewire.staff.song-queue-board', [
            'queue' => $songs->queue(),
            'recentDone' => SongRequest::query()
                ->whereIn('status', ['done', 'rejected'])
                ->latest('updated_at')
                ->limit(8)
                ->get(),
        ]);
    }
}
