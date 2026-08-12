<?php

namespace App\Livewire\Kds;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\QueryUseCases\GetKitchenQueueQueryUseCase;
use App\Domains\Order\UseCases\AdvanceKitchenTicketUseCase;
use App\Domains\Order\UseCases\ChangeOrderStatusUseCase;
use App\Domains\Order\UseCases\MarkOrderItemReadyUseCase;
use App\Domains\Order\UseCases\VoidOrderItemUseCase;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Board extends Component
{
    public string $activeTab = 'ongoing';

    #[On('echo-private:kds,OrderCreated')]
    #[On('echo-private:kds,OrderUpdated')]
    public function refreshBoard(): void
    {
    }

    public function setActiveTab(string $tab): void
    {
        if (in_array($tab, ['ongoing', 'ready', 'completed'])) {
            $this->activeTab = $tab;
        }
    }

    public function markAsReady(string $orderId, AdvanceKitchenTicketUseCase $advance): void
    {
        $advance->handle($orderId, OrderStatus::Ready);
    }

    public function markItemAsReady(string $orderId, string $itemId, MarkOrderItemReadyUseCase $markItem): void
    {
        $markItem->handle($orderId, $itemId);
    }

    public function markOrderAsServed(string $orderId, AdvanceKitchenTicketUseCase $advance): void
    {
        $advance->handle($orderId, OrderStatus::Served);
    }

    public function cancelOrder(string $orderId, ChangeOrderStatusUseCase $changeStatus): void
    {
        try {
            $changeStatus->handle($orderId, OrderStatus::Cancelled);
        } catch (ValidationException) {
            // Already settled, cancelled or gone — nothing to do.
        }
    }

    public function voidItem(string $orderId, string $itemId, VoidOrderItemUseCase $voidItem): void
    {
        $voidItem->handle($orderId, $itemId);
    }

    #[Computed]
    public function ongoingOrders()
    {
        return $this->queue()->ongoing();
    }

    #[Computed]
    public function readyOrders()
    {
        return $this->queue()->ready();
    }

    #[Computed]
    public function completedOrders()
    {
        return $this->queue()->completedToday();
    }

    /**
     * Computed properties do not receive method injection, so the read flow is
     * resolved from the container here — the component still never queries.
     */
    private function queue(): GetKitchenQueueQueryUseCase
    {
        return app(GetKitchenQueueQueryUseCase::class);
    }

    public function render()
    {
        return view('livewire.kds.board');
    }
}
