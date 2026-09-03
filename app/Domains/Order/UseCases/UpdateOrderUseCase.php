<?php

namespace App\Domains\Order\UseCases;

use App\Domains\Order\Actions\CalculateOrderTotalAction;
use App\Domains\Order\DTO\UpdateOrderData;
use App\Domains\Order\Repositories\OrderRepository;
use App\Events\OrderUpdated;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class UpdateOrderUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly CalculateOrderTotalAction $calculateTotal,
    ) {}

    public function handle(Order $order, UpdateOrderData $data): Order
    {
        $order = DB::transaction(function () use ($order, $data): Order {
            $totals = $this->calculateTotal->handle($data->items, $data->tax);

            return $this->orders->updateWithItems($order, [
                'table_id' => $data->tableId,
                'customer_name' => $data->customerName,
                'status' => $data->status->value,
                'notes' => $data->notes,
                'subtotal' => $totals->subtotal,
                'discount' => 0,
                'tax' => $totals->tax,
                'total' => $totals->total,
                'ordered_at' => $data->orderedAt ?? $order->ordered_at,
            ], $totals->items);
        });

        if ($data->status->isKitchenBound()) {
            OrderUpdated::dispatch($order);
        }

        return $order;
    }
}
