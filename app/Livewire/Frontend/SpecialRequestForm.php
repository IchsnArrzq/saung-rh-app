<?php

namespace App\Livewire\Frontend;

use App\Domains\Social\Enums\SpecialRequestCategory;
use App\Domains\Social\QueryUseCases\GetSpecialRequestBoardQueryUseCase;
use App\Domains\Social\UseCases\SubmitSpecialRequestUseCase;
use App\Domains\Table\QueryUseCases\GetTableSessionQueryUseCase;
use App\Support\TableSessionContext;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SpecialRequestForm extends Component
{
    public string $category = 'service';

    public string $description = '';

    #[Locked]
    public ?string $sessionId = null;

    public function mount(): void
    {
        $this->sessionId = TableSessionContext::sessionId();
        $this->category = SpecialRequestCategory::default()->value;
    }

    public function submit(SubmitSpecialRequestUseCase $submitRequest, GetTableSessionQueryUseCase $sessions): void
    {
        // Re-read on every send: a phone taken home loses access the moment
        // staff close the session, even with this panel still open.
        $session = $sessions->active(TableSessionContext::sessionId());

        if (! $session) {
            $this->addError('description', 'Sesi meja Anda sudah berakhir atau belum dikonfirmasi kasir. Scan QR di meja untuk mulai lagi.');

            return;
        }

        $validated = $this->validate([
            'category' => ['required', Rule::in(SpecialRequestCategory::values())],
            'description' => ['required', 'string', 'max:280'],
        ]);

        $submitRequest->handle(
            $session,
            SpecialRequestCategory::from($validated['category']),
            $validated['description'],
        );

        $this->reset('description');
        session()->flash('special_status', 'Permintaan dikirim. Menunggu persetujuan manajer.');
    }

    public function render(GetSpecialRequestBoardQueryUseCase $board): View
    {
        return view('livewire.frontend.special-request-form', [
            'mine' => $board->forSession($this->sessionId),
            'categories' => SpecialRequestCategory::options(),
        ]);
    }
}
