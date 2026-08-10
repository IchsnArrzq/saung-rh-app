<?php

namespace App\Domains\Order\UseCases;

use App\Domains\Order\DTO\CreateOrderData;
use App\Domains\Order\DTO\PlacePosOrderData;
use App\Domains\Order\Enums\OrderSource;
use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Events\OrderPlaced;
use App\Domains\Payment\Actions\RecordPaymentAction;
use App\Domains\Payment\Enums\PaymentMethod;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Counter sale rung up by a cashier: the ticket goes to the kitchen and the
 * money is usually taken straight away.
 *
 * The payment is created inline rather than through an event because it is part
 * of the same business fact — the caller needs it, and a half-recorded sale is
 * worse than a failed one. The table, by contrast, only *reacts* to the order,
 * so that crosses the boundary as an event (ARCHITECTURE.md § Domain
 * Dependencies).
 */
class PlacePosOrderUseCase
{
    private const SOURCE = OrderSource::Pos;

    public function __construct(
        private readonly CreateOrderUseCase $createOrder,
        private readonly RecordPaymentAction $recordPayment,
    ) {}

    public function handle(PlacePosOrderData $data): Order
    {
        $order = DB::transaction(function () use ($data): Order {
            $order = $this->createOrder->handle(new CreateOrderData(
                items: $data->items,
                status: OrderStatus::Confirmed,
                tableId: $data->tableId,
                customerName: $data->customerName,
                notes: self::SOURCE->composeNotes($data->notes),
            ));

            if ($data->payNow && (float) $order->total > 0) {
                $this->recordPayment->handle(
                    $order,
                    $data->paymentMethod ?? PaymentMethod::Cash,
                    (float) $order->total,
                    'POS',
                    'Payment otomatis dari POS.',
                );
            }

            return $order;
        });

        DB::afterCommit(fn () => OrderPlaced::dispatch($order->id, $order->table_id, self::SOURCE));

        return $order;
    }
}
