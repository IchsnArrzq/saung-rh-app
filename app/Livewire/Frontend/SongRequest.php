<?php

namespace App\Livewire\Frontend;

use App\Events\SongQueueUpdated;
use App\Models\SongRequest as SongRequestModel;
use App\Services\Songs\SongRequestService;
use App\Support\TableSessionContext;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use RuntimeException;

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

    public function submit(SongRequestService $songs): void
    {
        $session = TableSessionContext::activeSession();

        if (! $session) {
            $this->addError('title', 'Sesi meja tidak aktif. Silakan check-in via QR.');

            return;
        }

        $this->validate();

        try {
            $songs->request($session, $this->title, $this->artist, $this->requestedBy);
        } catch (RuntimeException $e) {
            $this->addError('title', $e->getMessage());

            return;
        }

        SongQueueUpdated::dispatch();

        $this->reset(['title', 'artist']);
        session()->flash('song_status', 'Lagu masuk antrean.');
    }

    public function render(SongRequestService $songs): View
    {
        $mine = $this->sessionId
            ? SongRequestModel::query()
                ->where('table_session_id', $this->sessionId)
                ->latest()
                ->limit(10)
                ->get()
            : collect();

        return view('livewire.frontend.song-request', [
            'mine' => $mine,
            'activeCount' => $this->sessionId ? $songs->activeCountForSession($this->sessionId) : 0,
            'queueMax' => $songs->queueMax(),
        ]);
    }
}
