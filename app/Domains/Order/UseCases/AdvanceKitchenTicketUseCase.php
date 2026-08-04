<?php

namespace App\Domains\Order\UseCases;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Repositories\OrderRepositoryInterface;
use App\Events\OrderUpdated;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Moves a whole kitchen ticket forward — the order and every line on it travel
 * together (ready, served). Silently ignores transitions the state machine
 * rejects so a double-click on the KDS cannot corrupt a ticket.
 */
class AdvanceKitchenTicketUseCase
{
    public function __construct(private readonly OrderRepositoryInterface $orders) {}

    public function handle(string $orderId, OrderStatus $next): ?Order
    {
        $order = $this->orders->find($orderId);

        if (! $order || ! OrderStatus::from($order->status)->canTransitionTo($next)) {
            return null;
        }

        $order = DB::transaction(function () use ($order, $next): Order {
            $updated = $this->orders->update($order, ['status' => $next->value]);
            $this->orders->updateItemsStatus($updated, $next->value);

            return $updated;
        });

        OrderUpdated::dispatch($order);

        return $order;
    }
}
