<?php

namespace App\Domains\Order\UseCases;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Repositories\OrderRepositoryInterface;
use App\Events\OrderUpdated;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Moves an order along its lifecycle, refusing transitions the state machine
 * does not allow (AGENTS.md § State Machine — hardcoded Enum + rules, not a
 * database-driven workflow).
 *
 * Used by the kitchen display (confirmed → preparing → ready → served) and by
 * anywhere else that flips a single status.
 */
class ChangeOrderStatusUseCase
{
    public function __construct(private readonly OrderRepositoryInterface $orders) {}

    public function handle(string $orderId, OrderStatus $next): Order
    {
        $order = $this->orders->find($orderId);

        if (! $order) {
            throw ValidationException::withMessages([
                'status' => 'Order tidak ditemukan.',
            ]);
        }

        $current = OrderStatus::from($order->status);

        if (! $current->canTransitionTo($next)) {
            throw ValidationException::withMessages([
                'status' => "Status tidak bisa berpindah dari {$current->label()} ke {$next->label()}.",
            ]);
        }

        $order = DB::transaction(fn (): Order => $this->orders->update($order, [
            'status' => $next->value,
        ]));

        if ($next->isKitchenBound()) {
            OrderUpdated::dispatch($order);
        }

        return $order;
    }
}
