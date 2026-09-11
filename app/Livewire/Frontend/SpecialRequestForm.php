<?php

namespace App\Livewire\Frontend;

use App\Domains\Social\QueryUseCases\GetSpecialRequestBoardQueryUseCase;
use App\Domains\Social\QueryUseCases\GetSpecialRequestCategoriesQueryUseCase;
use App\Domains\Social\UseCases\SubmitSpecialRequestUseCase;
use App\Domains\Table\QueryUseCases\GetTableSessionQueryUseCase;
use App\Events\FloorActivity;
use App\Support\TableSessionContext;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class SpecialRequestForm extends Component
{
    /** Dipilih tamu sendiri — tidak ada kategori yang terpilih otomatis. */
    public string $categoryId = '';

    public string $description = '';

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
     * Staf mengubah status atau menulis catatan → daftar "Permintaan Anda"
     * dirender ulang, dan tab panelnya diberi titik bila sedang tidak dibuka.
     *
     * @param  array<string, mixed>  $payload
     */
    #[On('echo:table.{tableId},FloorActivity')]
    public function onFloorActivity(array $payload = []): void
    {
        if (($payload['kind'] ?? null) === FloorActivity::REQUEST) {
            $this->dispatch('table-panel-activity', tab: 'permintaan');
        }
    }

    public function submit(
        SubmitSpecialRequestUseCase $submitRequest,
        GetSpecialRequestCategoriesQueryUseCase $categories,
        GetTableSessionQueryUseCase $sessions,
    ): void {
        // Re-read on every send: a phone taken home loses access the moment
        // staff close the session, even with this panel still open.
        $session = $sessions->active(TableSessionContext::sessionId());

        if (! $session) {
            $this->addError('description', 'Sesi meja Anda sudah berakhir atau belum dikonfirmasi kasir. Scan QR di meja untuk mulai lagi.');

            return;
        }

        $validated = $this->validate([
            'categoryId' => ['required', 'string'],
            'description' => ['required', 'string', 'max:280'],
        ], [
            'categoryId.required' => 'Pilih jenis permintaan dulu.',
            'description.required' => 'Tulis dulu apa yang Anda butuhkan.',
            'description.max' => 'Permintaan maksimal 280 karakter.',
        ]);

        $category = $categories->findActive($validated['categoryId']);

        if (! $category) {
            $this->addError('categoryId', 'Jenis permintaan ini sudah tidak tersedia. Pilih yang lain.');

            return;
        }

        $submitRequest->handle($session, $category, $validated['description']);

        $this->reset('description', 'categoryId');
        session()->flash('special_status', 'Permintaan terkirim ke pelayan.');
    }

    public function render(GetSpecialRequestBoardQueryUseCase $board, GetSpecialRequestCategoriesQueryUseCase $categories): View
    {
        return view('livewire.frontend.special-request-form', [
            'mine' => $board->forSession($this->sessionId),
            'categories' => $categories->active(),
        ]);
    }
}
