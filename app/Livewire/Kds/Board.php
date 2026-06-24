<?php

namespace App\Livewire\Kds;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
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

    public function markAsReady(string $orderId): void
    {
        $order = Order::find($orderId);
        
        if ($order && in_array($order->status, ['confirmed', 'preparing'])) {
            $order->update(['status' => 'ready']);
            $order->items()->update(['status' => 'ready']);
        }
    }

    public function markItemAsReady(string $orderId, string $itemId): void
    {
        $item = OrderItem::where('order_id', $orderId)->find($itemId);
        
        if ($item) {
            $item->update(['status' => 'ready']);
            
            $order = Order::with('items')->find($orderId);
            $allItemsReady = $order->items->every(fn($i) => $i->status === 'ready');
            
            if ($allItemsReady && in_array($order->status, ['confirmed', 'preparing'])) {
                $order->update(['status' => 'ready']);
            } elseif ($order->status === 'confirmed') {
                $order->update(['status' => 'preparing']);
            }
        }
    }

    public function markOrderAsServed(string $orderId): void
    {
        $order = Order::find($orderId);
        if ($order && $order->status === 'ready') {
            $order->update(['status' => 'served']);
            $order->items()->update(['status' => 'served']);
        }
    }

    public function cancelOrder(string $orderId): void
    {
        $order = Order::find($orderId);
        if ($order) {
            $order->update(['status' => 'cancelled']);
        }
    }

    public function voidItem(string $orderId, string $itemId): void
    {
        $order = Order::with('items')->find($orderId);
        
        if ($order) {
            $item = $order->items->find($itemId);
            
            if ($item) {
                $item->delete();
                
                $remainingItems = $order->items()->get();
                
                if ($remainingItems->isEmpty()) {
                    $order->update([
                        'status' => 'cancelled',
                        'subtotal' => 0,
                        'total' => 0
                    ]);
                } else {
                    $subtotal = $remainingItems->sum('line_total');
                    $total = max($subtotal + $order->tax - $order->discount, 0);
                    
                    $order->update([
                        'subtotal' => $subtotal,
                        'total' => $total
                    ]);
                }
            }
        }
    }

    #[Computed]
    public function ongoingOrders()
    {
        return Order::with(['items.menu', 'table'])
            ->withCount($this->vipItemsCount())
            ->whereIn('status', ['confirmed', 'preparing'])
            ->orderByDesc('vip_items_count') // VIP track jumps the queue
            ->orderBy('ordered_at', 'asc')
            ->get();
    }

    #[Computed]
    public function readyOrders()
    {
        return Order::with(['items.menu', 'table'])
            ->withCount($this->vipItemsCount())
            ->whereIn('status', ['ready'])
            ->orderBy('updated_at', 'asc')
            ->get();
    }

    #[Computed]
    public function completedOrders()
    {
        return Order::with(['items.menu', 'table'])
            ->withCount($this->vipItemsCount())
            ->whereIn('status', ['served', 'paid'])
            ->whereDate('updated_at', Carbon::today())
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();
    }

    /**
     * Reusable withCount definition that aggregates the number of VIP-track
     * items per order so the board can flag/prioritise VIP orders.
     *
     * @return array<string, callable>
     */
    private function vipItemsCount(): array
    {
        return [
            'items as vip_items_count' => fn ($query) => $query->whereHas('menu', fn ($menu) => $menu->where('track', 'vip')),
        ];
    }

    public function render()
    {
        return view('livewire.kds.board');
    }
}
