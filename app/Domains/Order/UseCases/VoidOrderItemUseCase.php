<?php

namespace App\Domains\Order\UseCases;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Repositories\OrderRepositoryInterface;
use App\Events\OrderUpdated;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Removes a line from a ticket and re-prices the order. Voiding the last
 * remaining line cancels the order outright rather than leaving a zero-value
 * ticket hanging in the kitchen.
 */
class VoidOrderItemUseCase
{
    public function __construct(private readonly OrderRepositoryInterface $orders) {}

    public function handle(string $orderId, string $itemId): ?Order
    {
        $item = $this->orders->findItem($orderId, $itemId);

        if (! $item) {
            return null;
        }

        $order = DB::transaction(function () use ($item, $orderId): ?Order {
            $this->orders->deleteItem($item);

            $order = $this->orders->findWithItems($orderId);

            if (! $order) {
                return null;
            }

            $remaining = $order->items;

            if ($remaining->isEmpty()) {
                return $this->orders->update($order, [
                    'status' => OrderStatus::Cancelled->value,
                    'subtotal' => 0,
                    'total' => 0,
                ]);
            }

            $subtotal = (float) $remaining->sum('line_total');

            return $this->orders->update($order, [
                'subtotal' => $subtotal,
                'total' => max($subtotal + (float) $order->tax - (float) $order->discount, 0),
            ]);
        });

        if ($order) {
            OrderUpdated::dispatch($order);
        }

        return $order;
    }
}
