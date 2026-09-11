<?php

namespace App\Livewire\Staff;

use App\Domains\Social\Enums\SongStatus;
use App\Domains\Social\Enums\SpecialRequestStatus;
use App\Domains\Social\QueryUseCases\GetFloorBoardQueryUseCase;
use App\Domains\Social\UseCases\AdvanceSongUseCase;
use App\Domains\Social\UseCases\ChangeSpecialRequestStatusUseCase;
use App\Domains\Social\UseCases\ClearTableChatUseCase;
use App\Domains\Social\UseCases\MarkTableChatSeenUseCase;
use App\Domains\Social\UseCases\NoteSpecialRequestUseCase;
use App\Domains\Social\UseCases\RejectSongUseCase;
use App\Domains\Social\UseCases\ReplyTableChatUseCase;
use App\Domains\Table\Enums\TableStatus;
use App\Events\FloorActivity;
use App\Models\SpecialRequest;
use App\Models\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use RuntimeException;

/**
 * Panel meja staf — pelayan membawanya di tablet atau ponsel, resepsionis dan
 * kasir memantaunya dari meja depan. Satu kartu per meja; membuka kartu
 * menampilkan permintaan khusus, lagu, dan obrolan meja itu dengan tombol besar
 * untuk menanganinya.
 *
 * Otorisasi tiga lapis: rute `can:viewAny,SpecialRequest`, authorize() di setiap
 * method tulis di bawah (method Livewire adalah endpoint HTTP tersendiri), dan
 * @can di Blade.
 */
class FloorBoard extends Component
{
    private const FILTERS = ['all', 'attention', 'occupied'];

    private const TABS = ['requests', 'songs', 'chat'];

    // Properties

    public string $search = '';

    /** all | attention | occupied */
    public string $filter = 'all';

    public ?string $selectedTableId = null;

    /** requests | songs | chat */
    public string $tab = 'requests';

    /** Permintaan yang form catatannya sedang terbuka. */
    public ?string $notingRequestId = null;

    public string $note = '';

    public string $reply = '';

    // Lifecycle

    public function mount(): void
    {
        $this->authorize('viewAny', SpecialRequest::class);
    }

    // Events

    /**
     * Setiap perubahan di lantai — permintaan baru, status berubah, pesan masuk —
     * merender ulang kartu. Permintaan baru juga memunculkan notifikasi.
     *
     * @param  array<string, mixed>  $payload
     */
    #[On('echo-private:floor,FloorActivity')]
    public function onFloorActivity(array $payload = []): void
    {
        $tableId = $payload['table_id'] ?? null;
        $kind = $payload['kind'] ?? null;

        // Tab obrolan meja ini sedang terbuka di layar: pesan barunya sudah terbaca.
        if ($kind === FloorActivity::CHAT && $tableId && $tableId === $this->selectedTableId && $this->tab === 'chat') {
            app(MarkTableChatSeenUseCase::class)->handle((string) auth()->id(), (string) $tableId);
        }

        if ($kind === FloorActivity::REQUEST && ($payload['action'] ?? null) === FloorActivity::NEW) {
            $this->dispatch(
                'floor-toast',
                message: 'Permintaan baru di Meja '.($payload['table_code'] ?? '—'),
                tone: 'attention',
                tableId: $tableId,
            );
        }
    }

    // Actions

    public function updatedFilter(): void
    {
        if (! in_array($this->filter, self::FILTERS, true)) {
            $this->filter = 'all';
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'filter');
    }

    public function open(string $tableId, string $tab = 'requests'): void
    {
        $this->selectedTableId = $tableId;
        $this->resetPanel();
        $this->showTab($tab);
    }

    public function close(): void
    {
        $this->selectedTableId = null;
        $this->resetPanel();
    }

    public function setTab(string $tab): void
    {
        $this->resetPanel();
        $this->showTab($tab);
    }

    public function claimRequest(string $id, ChangeSpecialRequestStatusUseCase $change, GetFloorBoardQueryUseCase $board): void
    {
        $this->moveRequest($id, SpecialRequestStatus::Assigned, $change, $board);
    }

    public function completeRequest(string $id, ChangeSpecialRequestStatusUseCase $change, GetFloorBoardQueryUseCase $board): void
    {
        $this->moveRequest($id, SpecialRequestStatus::Done, $change, $board);
    }

    public function startNote(string $id, GetFloorBoardQueryUseCase $board): void
    {
        $request = $this->authorizedRequest($id, $board);

        if (! $request) {
            return;
        }

        $this->resetErrorBag();
        $this->notingRequestId = (string) $request->id;
        $this->note = (string) ($request->staff_note ?? '');
    }

    public function cancelNote(): void
    {
        $this->reset('notingRequestId', 'note');
        $this->resetErrorBag('note');
    }

    public function saveNote(NoteSpecialRequestUseCase $noteRequest, GetFloorBoardQueryUseCase $board): void
    {
        $request = $this->authorizedRequest((string) $this->notingRequestId, $board);

        if (! $request) {
            return;
        }

        $this->validateNote();

        try {
            $noteRequest->handle((string) $request->id, $this->note);
        } catch (ValidationException $e) {
            $this->addError('note', $this->firstError($e));

            return;
        }

        $this->cancelNote();
        $this->toast('Catatan disimpan dan terlihat oleh tamu.');
    }

    public function rejectRequest(ChangeSpecialRequestStatusUseCase $change, GetFloorBoardQueryUseCase $board): void
    {
        $this->validateNote('Tulis alasan singkat supaya tamu tahu kenapa permintaannya tidak bisa dipenuhi.');

        if ($this->moveRequest((string) $this->notingRequestId, SpecialRequestStatus::Rejected, $change, $board, $this->note)) {
            $this->cancelNote();
        }
    }

    public function advanceSong(string $id, AdvanceSongUseCase $advanceSong, GetFloorBoardQueryUseCase $board): void
    {
        $song = $board->song($id);

        if (! $song) {
            $this->addError('panel', 'Lagu tidak ditemukan. Muat ulang panel.');

            return;
        }

        $this->authorize('update', $song);

        $song = $advanceSong->handle((string) $song->id);

        $this->toast($song->status === SongStatus::Playing ? 'Lagu mulai diputar.' : 'Lagu ditandai selesai.');
    }

    public function rejectSong(string $id, RejectSongUseCase $rejectSong, GetFloorBoardQueryUseCase $board): void
    {
        $song = $board->song($id);

        if (! $song) {
            $this->addError('panel', 'Lagu tidak ditemukan. Muat ulang panel.');

            return;
        }

        $this->authorize('update', $song);

        $rejectSong->handle((string) $song->id);

        $this->toast('Lagu dikeluarkan dari antrean.');
    }

    public function sendReply(ReplyTableChatUseCase $replyToTable, GetFloorBoardQueryUseCase $board): void
    {
        $table = $this->authorizedChatTable($board);

        if (! $table) {
            return;
        }

        $this->validate(['reply' => ['required', 'string', 'max:280']], [
            'reply.required' => 'Tulis pesan dulu.',
            'reply.max' => 'Pesan maksimal 280 karakter.',
        ]);

        try {
            $replyToTable->handle($table, auth()->user(), $this->reply);
        } catch (RuntimeException $e) {
            $this->addError('reply', $e->getMessage());

            return;
        }

        $this->reset('reply');
    }

    public function clearChat(ClearTableChatUseCase $clearChat, GetFloorBoardQueryUseCase $board): void
    {
        $table = $this->authorizedChatTable($board);

        if (! $table) {
            return;
        }

        try {
            $clearChat->handle($table);
        } catch (RuntimeException $e) {
            $this->addError('panel', $e->getMessage());

            return;
        }

        $this->toast('Obrolan Meja '.$table->code.' dibersihkan.');
    }

    // Render

    public function render(GetFloorBoardQueryUseCase $board): View
    {
        $all = $this->decorate($board->tiles((string) auth()->id()));

        $detail = $this->selectedTableId ? $board->detail($this->selectedTableId) : null;
        $selectedTile = $detail ? $all->first(fn (array $tile) => $tile['table']->id === $this->selectedTableId) : null;

        return view('livewire.staff.floor-board', [
            'tiles' => $this->visible($all),
            'totalTables' => $all->count(),
            'counts' => [
                'all' => $all->count(),
                'attention' => $all->where('attention', true)->count(),
                'occupied' => $all->where('occupied', true)->count(),
            ],
            'detail' => $detail,
            'unreadChat' => (bool) ($selectedTile['unread_chat'] ?? false),
        ]);
    }

    // Custom methods

    /**
     * Tanda yang dibaca view: perlu tindakan, terisi, dan tab yang dibuka saat
     * kartunya ditekan (yang paling butuh perhatian lebih dulu).
     *
     * @param  Collection<int, array<string, mixed>>  $tiles
     * @return Collection<int, array<string, mixed>>
     */
    private function decorate(Collection $tiles): Collection
    {
        return $tiles->map(function (array $tile): array {
            $tile['attention'] = $tile['open_requests'] > 0 || $tile['unread_chat'];
            $tile['occupied'] = $tile['session'] !== null
                || in_array((string) $tile['table']->status, [TableStatus::Occupied->value, TableStatus::OrderIn->value], true);
            $tile['default_tab'] = match (true) {
                $tile['open_requests'] > 0 => 'requests',
                $tile['unread_chat'] => 'chat',
                $tile['active_songs'] > 0 => 'songs',
                default => 'requests',
            };

            return $tile;
        });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $tiles
     * @return Collection<int, array<string, mixed>>
     */
    private function visible(Collection $tiles): Collection
    {
        $search = Str::lower(trim($this->search));

        return $tiles
            ->filter(fn (array $tile): bool => match ($this->filter) {
                'attention' => $tile['attention'],
                'occupied' => $tile['occupied'],
                default => true,
            })
            ->filter(function (array $tile) use ($search): bool {
                if ($search === '') {
                    return true;
                }

                $table = $tile['table'];

                return Str::contains(
                    Str::lower($table->code.' '.$table->name.' '.($table->tableCategory?->name ?? '')),
                    $search,
                );
            })
            ->values();
    }

    private function showTab(string $tab): void
    {
        $this->tab = in_array($tab, self::TABS, true) ? $tab : 'requests';

        if ($this->tab === 'chat' && $this->selectedTableId) {
            app(MarkTableChatSeenUseCase::class)->handle((string) auth()->id(), $this->selectedTableId);
        }
    }

    private function resetPanel(): void
    {
        $this->reset('notingRequestId', 'note', 'reply');
        $this->resetErrorBag();
    }

    private function moveRequest(
        string $id,
        SpecialRequestStatus $target,
        ChangeSpecialRequestStatusUseCase $change,
        GetFloorBoardQueryUseCase $board,
        ?string $note = null,
    ): bool {
        $request = $this->authorizedRequest($id, $board);

        if (! $request) {
            return false;
        }

        try {
            $change->handle((string) $request->id, $target, auth()->user(), $note);
        } catch (ValidationException $e) {
            $this->addError(array_key_exists('note', $e->errors()) ? 'note' : 'panel', $this->firstError($e));

            return false;
        }

        $this->toast(match ($target) {
            SpecialRequestStatus::Assigned => 'Permintaan ditandai sedang ditangani.',
            SpecialRequestStatus::Rejected => 'Permintaan ditolak. Alasannya terlihat oleh tamu.',
            default => 'Permintaan selesai.',
        });

        return true;
    }

    private function authorizedRequest(string $id, GetFloorBoardQueryUseCase $board): ?SpecialRequest
    {
        $request = $board->request($id);

        if (! $request) {
            $this->addError('panel', 'Permintaan tidak ditemukan. Tutup panel lalu buka lagi.');

            return null;
        }

        $this->authorize('update', $request);

        return $request;
    }

    private function authorizedChatTable(GetFloorBoardQueryUseCase $board): ?Table
    {
        $table = $this->selectedTableId ? $board->table($this->selectedTableId) : null;

        if (! $table) {
            $this->addError('panel', 'Meja tidak ditemukan. Tutup panel lalu buka lagi.');

            return null;
        }

        $this->authorize('moderateChat', $table);

        return $table;
    }

    private function validateNote(?string $requiredMessage = null): void
    {
        $this->validate(['note' => ['required', 'string', 'max:280']], [
            'note.required' => $requiredMessage ?? 'Catatan masih kosong.',
            'note.max' => 'Catatan maksimal 280 karakter.',
        ]);
    }

    private function firstError(ValidationException $e): string
    {
        return (string) collect($e->errors())->flatten()->first();
    }

    private function toast(string $message): void
    {
        $this->dispatch('floor-toast', message: $message, tone: 'success', tableId: null);
    }
}
