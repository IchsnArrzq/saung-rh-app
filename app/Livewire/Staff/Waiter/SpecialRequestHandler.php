<?php

namespace App\Livewire\Staff\Waiter;

use App\Models\SpecialRequest;
use App\Services\SpecialRequests\SpecialRequestService;
use Illuminate\View\View;
use Livewire\Component;

class SpecialRequestHandler extends Component
{
    public function complete(SpecialRequestService $service, string $id): void
    {
        $request = SpecialRequest::query()
            ->where('assigned_to', auth()->id())
            ->findOrFail($id);

        $service->complete($request);
        session()->flash('special_status', 'Permintaan ditandai selesai.');
    }

    public function render(): View
    {
        $waiterId = (string) auth()->id();

        return view('livewire.staff.waiter.special-request-handler', [
            'assigned' => SpecialRequest::query()->openFor($waiterId)->latest()->get(),
            'doneToday' => SpecialRequest::query()
                ->where('assigned_to', $waiterId)
                ->where('status', 'done')
                ->whereDate('handled_at', today())
                ->count(),
        ]);
    }
}
