<?php

namespace App\Livewire\Frontend;

use App\Models\Order;
use App\Support\RestaurantCart;
use Illuminate\Support\Collection;
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

    public function render(): View
    {
        $orders = collect();
        $queueTotal = 0;

        if ($this->tableId) {
            // Ongoing kitchen queue, ordered exactly like the KDS board so the
            // position we show the guest matches what the kitchen works from.
            $queue = Order::query()
                ->withCount(['items as vip_items_count' => fn ($q) => $q->whereHas('menu', fn ($m) => $m->where('track', 'vip'))])
                ->whereIn('status', ['confirmed', 'preparing'])
                ->orderByDesc('vip_items_count')
                ->orderBy('ordered_at')
                ->get(['id', 'status', 'ordered_at']);

            $queueTotal = $queue->count();

            /** @var Collection<string,int> $positions */
            $positions = $queue->values()->mapWithKeys(fn (Order $o, int $i) => [$o->id => $i + 1]);

            $orders = Order::query()
                ->with(['items:id,order_id,menu_name_snapshot,qty,status'])
                ->where('table_id', $this->tableId)
                ->whereIn('status', ['confirmed', 'preparing', 'ready'])
                ->whereDate('ordered_at', today())
                ->orderByDesc('ordered_at')
                ->get()
                ->each(function (Order $order) use ($positions): void {
                    $order->setAttribute('queue_position', $positions->get($order->id));
                });
        }

        return view('livewire.frontend.order-status', [
            'orders' => $orders,
            'queueTotal' => $queueTotal,
        ]);
    }
}
