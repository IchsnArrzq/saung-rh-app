<?php

namespace App\Livewire\Staff\Waiter;

use App\Domains\Social\QueryUseCases\GetSpecialRequestBoardQueryUseCase;
use App\Domains\Social\UseCases\CompleteSpecialRequestUseCase;
use Illuminate\View\View;
use Livewire\Component;

class SpecialRequestHandler extends Component
{
    public function complete(CompleteSpecialRequestUseCase $completeRequest, string $id): void
    {
        // The UseCase scopes the lookup to the signed-in waiter, so one waiter
        // cannot close another's job.
        $completeRequest->handle($id, (string) auth()->id());

        session()->flash('special_status', 'Permintaan ditandai selesai.');
    }

    public function render(GetSpecialRequestBoardQueryUseCase $board): View
    {
        return view('livewire.staff.waiter.special-request-handler', $board->forWaiter((string) auth()->id()));
    }
}
