<?php

namespace App\Livewire\Frontend;

use App\Models\SpecialRequest;
use App\Services\SpecialRequests\SpecialRequestService;
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
    }

    public function submit(SpecialRequestService $service): void
    {
        $session = TableSessionContext::activeSession();

        if (! $session) {
            $this->addError('description', 'Sesi meja tidak aktif. Silakan check-in via QR.');

            return;
        }

        $this->validate([
            'category' => ['required', Rule::in(array_keys(SpecialRequest::CATEGORIES))],
            'description' => ['required', 'string', 'max:280'],
        ]);

        $service->submit($session, $this->category, $this->description);

        $this->reset('description');
        session()->flash('special_status', 'Permintaan dikirim. Menunggu persetujuan manajer.');
    }

    public function render(): View
    {
        $mine = $this->sessionId
            ? SpecialRequest::query()
                ->where('table_session_id', $this->sessionId)
                ->latest()
                ->limit(8)
                ->get()
            : collect();

        return view('livewire.frontend.special-request-form', [
            'mine' => $mine,
            'categories' => SpecialRequest::CATEGORIES,
        ]);
    }
}
