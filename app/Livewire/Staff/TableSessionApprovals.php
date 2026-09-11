<?php

namespace App\Livewire\Staff;

use App\Domains\Table\QueryUseCases\GetTableSessionQueryUseCase;
use App\Domains\Table\UseCases\ApproveTableSessionUseCase;
use App\Domains\Table\UseCases\RejectTableSessionUseCase;
use App\Models\TableSession;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Guests who scanned a table's QR and are waiting for someone to confirm they
 * are really sitting there. Embedded on the cashier's POS screens, the
 * receptionist's dashboard and floor map, and the admin session history.
 *
 * `compact` renders nothing while nobody is waiting — on a POS screen an empty
 * tray is only noise.
 */
class TableSessionApprovals extends Component
{
    #[Locked]
    public bool $compact = false;

    public function mount(bool $compact = false): void
    {
        $this->authorize('viewAny', TableSession::class);

        $this->compact = $compact;
    }

    /** Realtime nudge; the view's wire:poll covers for it when Reverb is down. */
    #[On('echo-private:table-sessions,TableSessionsChanged')]
    public function refreshList(): void
    {
        // Re-render reads the waiting list again.
    }

    public function approve(string $sessionId, GetTableSessionQueryUseCase $sessions, ApproveTableSessionUseCase $approve): void
    {
        $session = $sessions->find($sessionId);

        if (! $session) {
            return;
        }

        $this->authorize('update', $session);

        $approve->handle($session, auth()->user());

        session()->flash('table_session_status', 'Meja '.$session->table?->code.' atas nama '.$session->customer_name.' disetujui.');
    }

    public function reject(string $sessionId, GetTableSessionQueryUseCase $sessions, RejectTableSessionUseCase $reject): void
    {
        $session = $sessions->find($sessionId);

        if (! $session) {
            return;
        }

        $this->authorize('update', $session);

        $reject->handle($session, auth()->user());

        session()->flash('table_session_status', 'Permintaan Meja '.$session->table?->code.' ditolak.');
    }

    public function render(GetTableSessionQueryUseCase $sessions): View
    {
        return view('livewire.staff.table-session-approvals', [
            'pending' => $sessions->pending(),
        ]);
    }
}
