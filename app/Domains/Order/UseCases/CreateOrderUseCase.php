<?php

namespace App\Domains\Order\UseCases;

use App\Domains\Order\Actions\CalculateOrderTotalAction;
use App\Domains\Order\Actions\GenerateOrderNumberAction;
use App\Domains\Order\DTO\CreateOrderData;
use App\Domains\Order\Repositories\OrderRepositoryInterface;
use App\Events\OrderCreated;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Creates one order with its lines. Transaction boundary lives here
 * (AGENTS.md § UseCase).
 */
class CreateOrderUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly CalculateOrderTotalAction $calculateTotal,
        private readonly GenerateOrderNumberAction $generateOrderNumber,
    ) {}

    public function handle(CreateOrderData $data): Order
    {
        $order = DB::transaction(function () use ($data): Order {
            $totals = $this->calculateTotal->handle($data->items, $data->tax);

            return $this->orders->createWithItems([
                'cashier_id' => $data->cashierId ?? Auth::id(),
                'customer_id' => $data->customerId,
                'table_id' => $data->tableId,
                'order_number' => $this->generateOrderNumber->handle(),
                'customer_name' => $data->customerName,
                'status' => $data->status->value,
                'notes' => $data->notes,
                'subtotal' => $totals->subtotal,
                'discount' => 0,
                'tax' => $totals->tax,
                'total' => $totals->total,
                'ordered_at' => $data->orderedAt ?? now(),
            ], $totals->items);
        });

        // Broadcast only once the row is committed, and only for states the
        // kitchen actually reacts to. afterCommit (not a plain dispatch) because
        // the flow UseCases wrap this one in an outer transaction — without it
        // the KDS would be told about an order that can still roll back.
        if ($data->status->isKitchenBound()) {
            DB::afterCommit(fn () => OrderCreated::dispatch($order));
        }

        return $order;
    }
}
