<?php

namespace App\Livewire\Frontend;

use App\Domains\Social\Enums\SpecialRequestCategory;
use App\Domains\Social\QueryUseCases\GetSpecialRequestBoardQueryUseCase;
use App\Domains\Social\UseCases\SubmitSpecialRequestUseCase;
use App\Support\TableSessionContext;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class SpecialRequestForm extends Component
{
    public string $category = 'service';

    public string $description = '';

    public ?string $sessionId = null;

    public function mount(): void
    {
        $this->sessionId = TableSessionContext::current()['session_id'] ?? null;
        $this->category = SpecialRequestCategory::default()->value;
    }

    public function submit(SubmitSpecialRequestUseCase $submitRequest): void
    {
        $session = TableSessionContext::activeSession();

        if (! $session) {
            $this->addError('description', 'Sesi meja tidak aktif. Silakan check-in via QR.');

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
