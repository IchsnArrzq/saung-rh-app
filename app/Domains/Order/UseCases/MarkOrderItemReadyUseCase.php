<?php

namespace App\Domains\Order\UseCases;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Repositories\OrderRepositoryInterface;
use App\Events\OrderUpdated;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Marks one line of a ticket as plated, then derives the order's own status:
 * every line ready promotes the order to Ready, otherwise a still-confirmed
 * order slips into Preparing.
 */
class MarkOrderItemReadyUseCase
{
    public function __construct(private readonly OrderRepositoryInterface $orders) {}

    public function handle(string $orderId, string $itemId): ?Order
    {
        $item = $this->orders->findItem($orderId, $itemId);

        if (! $item) {
            return null;
        }

        $order = DB::transaction(function () use ($item, $orderId): ?Order {
            $item->update(['status' => OrderStatus::Ready->value]);

            $order = $this->orders->findWithItems($orderId);

            if (! $order) {
                return null;
            }

            $current = $order->status;
            $allReady = $order->items->every(
                fn ($line) => $line->status === OrderStatus::Ready->value
            );

            $next = match (true) {
                $allReady => OrderStatus::Ready,
                $current === OrderStatus::Confirmed => OrderStatus::Preparing,
                default => null,
            };

            if ($next && $current->canTransitionTo($next)) {
                return $this->orders->update($order, ['status' => $next->value]);
            }

            return $order;
        });

        if ($order) {
            OrderUpdated::dispatch($order);
        }

        return $order;
    }
}
