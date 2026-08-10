<?php

namespace App\Livewire\Frontend;

use App\Domains\Order\QueryUseCases\GetTableOrderTrackingQueryUseCase;
use App\Support\RestaurantCart;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Read-only order tracker for the table bound by the customer's QR check-in.
 * Shows each active order's kitchen status, how long it has been waiting, and
 * its position in the kitchen queue (mirroring the KDS ordering).
 */
class OrderStatus extends Component
{
    public ?string $tableId = null;

    public function mount(): void
    {
        $this->tableId = RestaurantCart::context()['table_id'] ?? null;
    }

    public function render(GetTableOrderTrackingQueryUseCase $tracking): View
    {
        return view('livewire.frontend.order-status', $tracking->handle($this->tableId));
    }
}
