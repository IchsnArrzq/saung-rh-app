<?php

namespace App\Livewire\Staff\Manager;

use App\Domains\Social\QueryUseCases\GetSpecialRequestBoardQueryUseCase;
use App\Domains\Social\UseCases\ApproveSpecialRequestUseCase;
use App\Domains\Social\UseCases\RejectSpecialRequestUseCase;
use Illuminate\View\View;
use Livewire\Component;

class SpecialRequestApprover extends Component
{
    public function approve(ApproveSpecialRequestUseCase $approveRequest, string $id): void
    {
        $waiter = $approveRequest->handle($id, auth()->user())->assignee;

        session()->flash('special_status', $waiter
            ? "Disetujui & ditugaskan ke {$waiter->name}."
            : 'Disetujui (belum ada waiter tersedia untuk ditugaskan).');
    }

    public function reject(RejectSpecialRequestUseCase $rejectRequest, string $id): void
    {
        $rejectRequest->handle($id, auth()->user());
        session()->flash('special_status', 'Permintaan ditolak.');
    }

    public function render(GetSpecialRequestBoardQueryUseCase $board): View
    {
        return view('livewire.staff.manager.special-request-approver', $board->forManager());
    }
}
