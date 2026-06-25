<?php

namespace App\Livewire\Staff\Manager;

use App\Models\SpecialRequest;
use App\Services\SpecialRequests\SpecialRequestService;
use Illuminate\View\View;
use Livewire\Component;

class SpecialRequestApprover extends Component
{
    public function approve(SpecialRequestService $service, string $id): void
    {
        $request = SpecialRequest::query()->findOrFail($id);
        $waiter = $service->approve($request, auth()->user())->assignee;

        session()->flash('special_status', $waiter
            ? "Disetujui & ditugaskan ke {$waiter->name}."
            : 'Disetujui (belum ada waiter tersedia untuk ditugaskan).');
    }

    public function reject(SpecialRequestService $service, string $id): void
    {
        $service->reject(SpecialRequest::query()->findOrFail($id), auth()->user());
        session()->flash('special_status', 'Permintaan ditolak.');
    }

    public function render(): View
    {
        return view('livewire.staff.manager.special-request-approver', [
            'pending' => SpecialRequest::query()->pending()->latest()->get(),
            'recent' => SpecialRequest::query()
                ->whereIn('status', ['approved', 'assigned', 'done', 'rejected'])
                ->with('assignee')
                ->latest('updated_at')
                ->limit(10)
                ->get(),
        ]);
    }
}
