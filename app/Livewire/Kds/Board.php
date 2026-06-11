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
        return Order::with(['items', 'table'])
            ->whereIn('status', ['confirmed', 'preparing'])
            ->orderBy('ordered_at', 'asc')
            ->get();
    }

    #[Computed]
    public function readyOrders()
    {
        return Order::with(['items', 'table'])
            ->whereIn('status', ['ready'])
            ->orderBy('updated_at', 'asc')
            ->get();
    }

    #[Computed]
    public function completedOrders()
    {
        return Order::with(['items', 'table'])
            ->whereIn('status', ['served', 'paid'])
            ->whereDate('updated_at', Carbon::today())
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();
    }

    public function render()
    {
        return view('livewire.kds.board');
    }
}
