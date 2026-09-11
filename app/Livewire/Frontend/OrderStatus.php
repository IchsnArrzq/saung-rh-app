<?php

namespace App\Livewire\Frontend;

use App\Domains\Order\QueryUseCases\GetTableOrderTrackingQueryUseCase;
use App\Domains\Table\QueryUseCases\GetTableSessionQueryUseCase;
use App\Support\TableSessionContext;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Read-only order tracker for the table bound by the guest's QR session.
 * Shows each active order's kitchen status, how long it has been waiting, and
 * its position in the kitchen queue (mirroring the KDS ordering).
 *
 * It tracks only while that session is still active: a phone left on this
 * panel after the party paid would otherwise keep watching the next party's
 * orders at the same table.
 */
class OrderStatus extends Component
{
    #[Locked]
    public ?string $tableId = null;

    public function mount(): void
    {
        $this->tableId = TableSessionContext::current()['table_id'] ?? null;
    }

    public function render(GetTableOrderTrackingQueryUseCase $tracking, GetTableSessionQueryUseCase $sessions): View
    {
        $session = $sessions->active(TableSessionContext::sessionId());
        $sessionOpen = $session !== null && $session->table_id === $this->tableId;

        return view('livewire.frontend.order-status', [
            ...$tracking->handle($sessionOpen ? $this->tableId : null),
            'sessionOpen' => $sessionOpen,
        ]);
    }
}
