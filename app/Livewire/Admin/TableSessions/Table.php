<?php

namespace App\Livewire\Admin\TableSessions;

use App\Domains\Table\Enums\TableSessionStatus;
use App\Domains\Table\QueryUseCases\GetTableSessionQueryUseCase;
use App\Domains\Table\UseCases\CloseTableSessionUseCase;
use App\Models\TableSession;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * History of every QR table session: which table, under whose name, who let
 * them in, and how it ended. Staff can end a live session from here.
 */
class Table extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    public function mount(): void
    {
        $this->authorize('viewAny', TableSession::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    /** Scans and approvals elsewhere change this list too. */
    #[On('echo-private:table-sessions,TableSessionsChanged')]
    public function refreshList(): void
    {
        // Re-render reads the page again.
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'status');
        $this->resetPage();
    }

    public function close(string $sessionId, GetTableSessionQueryUseCase $sessions, CloseTableSessionUseCase $close): void
    {
        $session = $sessions->find($sessionId);

        if (! $session) {
            return;
        }

        $this->authorize('update', $session);

        $close->handle($session, auth()->user());

        session()->flash('success', 'Sesi Meja '.$session->table?->code.' atas nama '.$session->customer_name.' dinonaktifkan.');
    }

    public function render(GetTableSessionQueryUseCase $sessions): View
    {
        return view('livewire.admin.table-sessions.table', [
            'sessions' => $sessions->paginate($this->search, TableSessionStatus::tryFrom($this->status)),
            'statusOptions' => TableSessionStatus::options(),
        ]);
    }
}
