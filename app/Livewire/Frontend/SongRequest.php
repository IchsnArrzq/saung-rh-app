<?php

namespace App\Livewire\Frontend;

use App\Domains\Social\QueryUseCases\GetSongQueueQueryUseCase;
use App\Domains\Social\UseCases\RequestSongUseCase;
use App\Support\TableSessionContext;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class SongRequest extends Component
{
    #[Validate('required|string|max:120')]
    public string $title = '';

    #[Validate('nullable|string|max:120')]
    public string $artist = '';

    #[Validate('nullable|string|max:60')]
    public string $requestedBy = '';

    public ?string $sessionId = null;

    public function mount(): void
    {
        $this->sessionId = TableSessionContext::current()['session_id'] ?? null;
    }

    public function submit(RequestSongUseCase $requestSong): void
    {
        $session = TableSessionContext::activeSession();

        if (! $session) {
            $this->addError('title', 'Sesi meja tidak aktif. Silakan check-in via QR.');

            return;
        }

        $this->validate();

        try {
            $requestSong->handle($session, $this->title, $this->artist, $this->requestedBy);
        } catch (ValidationException $e) {
            $this->addError('title', $e->validator->errors()->first());

            return;
        }

        $this->reset(['title', 'artist']);
        session()->flash('song_status', 'Lagu masuk antrean.');
    }

    public function render(GetSongQueueQueryUseCase $songs, RequestSongUseCase $requestSong): View
    {
        return view('livewire.frontend.song-request', [
            'mine' => $songs->forSession($this->sessionId),
            'activeCount' => $songs->activeCountForSession($this->sessionId),
            'queueMax' => $requestSong->queueMax(),
        ]);
    }
}
