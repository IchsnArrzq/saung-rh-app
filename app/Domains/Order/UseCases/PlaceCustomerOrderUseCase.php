<?php

namespace App\Domains\Order\UseCases;

use App\Domains\Order\DTO\CreateOrderData;
use App\Domains\Order\DTO\PlaceCustomerOrderData;
use App\Domains\Order\Enums\OrderSource;
use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Events\OrderPlaced;
use App\Domains\Table\QueryUseCases\FindTableQueryUseCase;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A round ordered from the logged-in customer portal.
 *
 * Unlike the anonymous QR ticket this one is attributed to a user account, and
 * it refuses a table that can no longer take orders — the customer picked that
 * table minutes ago and the floor may have moved on since. That check is a
 * *read* of the Table domain; the write that follows is left to the Table
 * domain's own listener on OrderPlaced.
 */
class PlaceCustomerOrderUseCase
{
    private const SOURCE = OrderSource::CustomerPortal;

    public function __construct(
        private readonly CreateOrderUseCase $createOrder,
        private readonly FindTableQueryUseCase $findTable,
    ) {}

    public function handle(PlaceCustomerOrderData $data): Order
    {
        if ($data->items === []) {
            throw ValidationException::withMessages([
                'cart' => 'Cart masih kosong.',
            ]);
        }

        $order = DB::transaction(function () use ($data): Order {
            $table = $this->findTable->orderable($data->tableId);

            if (! $table) {
                throw ValidationException::withMessages([
                    'table_id' => 'Meja tidak bisa menerima pesanan saat ini. Pilih meja lain.',
                ]);
            }

            return $this->createOrder->handle(new CreateOrderData(
                items: $data->items,
                status: OrderStatus::Confirmed,
                tableId: $table->id,
                customerName: $data->customerName ?? Auth::user()?->name,
                notes: self::SOURCE->composeNotes($data->notes),
                customerId: $data->customerId ?? Auth::id(),
            ));
        });

        DB::afterCommit(fn () => OrderPlaced::dispatch($order->id, $order->table_id, self::SOURCE));

        return $order;
    }
}
