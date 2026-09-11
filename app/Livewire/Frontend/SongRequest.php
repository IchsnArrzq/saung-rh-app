<?php

namespace App\Livewire\Frontend;

use App\Domains\Social\QueryUseCases\GetSongQueueQueryUseCase;
use App\Domains\Social\UseCases\RequestSongUseCase;
use App\Domains\Table\QueryUseCases\GetTableSessionQueryUseCase;
use App\Events\FloorActivity;
use App\Support\TableSessionContext;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
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

    #[Locked]
    public ?string $sessionId = null;

    /** Kanal siaran meja ini: dari sesi QR, tidak pernah dari isian tamu. */
    #[Locked]
    public ?string $tableId = null;

    public function mount(): void
    {
        $context = TableSessionContext::current();

        $this->sessionId = $context['session_id'] ?? null;
        $this->tableId = $context['table_id'] ?? null;
    }

    /**
     * Staf memutar atau menolak lagu meja ini → daftar "Lagu dari meja Anda"
     * dirender ulang, dan tab Lagu diberi titik bila sedang tidak dibuka.
     *
     * @param  array<string, mixed>  $payload
     */
    #[On('echo:table.{tableId},FloorActivity')]
    public function onFloorActivity(array $payload = []): void
    {
        if (($payload['kind'] ?? null) === FloorActivity::SONG) {
            $this->dispatch('table-panel-activity', tab: 'lagu');
        }
    }

    public function submit(RequestSongUseCase $requestSong, GetTableSessionQueryUseCase $sessions): void
    {
        // Re-read on every send: a phone taken home loses access the moment
        // staff close the session, even with this panel still open.
        $session = $sessions->active(TableSessionContext::sessionId());

        if (! $session) {
            $this->addError('title', 'Sesi meja Anda sudah berakhir atau belum dikonfirmasi kasir. Scan QR di meja untuk mulai lagi.');

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

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'title.required' => 'Tulis judul lagunya dulu.',
            'title.max' => 'Judul lagu maksimal 120 karakter.',
            'artist.max' => 'Nama penyanyi maksimal 120 karakter.',
            'requestedBy.max' => 'Nama maksimal 60 karakter.',
        ];
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
