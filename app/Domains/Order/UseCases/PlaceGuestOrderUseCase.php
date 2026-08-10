<?php

namespace App\Domains\Order\UseCases;

use App\Domains\Order\DTO\CreateOrderData;
use App\Domains\Order\DTO\PlaceGuestOrderData;
use App\Domains\Order\Enums\OrderSource;
use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Events\OrderPlaced;
use App\Domains\Table\Repositories\TableRepositoryInterface;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Dine-in ticket sent by a guest from the QR menu. No account, no payment —
 * the cashier settles the table later (SettleBillUseCase).
 *
 * The table is read here (to name the guest after it) but never written: the
 * Table domain reacts to OrderPlaced on its own.
 */
class PlaceGuestOrderUseCase
{
    private const SOURCE = OrderSource::DineInQr;

    public function __construct(
        private readonly CreateOrderUseCase $createOrder,
        private readonly TableRepositoryInterface $tables,
    ) {}

    public function handle(PlaceGuestOrderData $data): Order
    {
        $order = DB::transaction(function () use ($data): Order {
            $table = $this->tables->find($data->tableId);

            if (! $table) {
                throw ValidationException::withMessages([
                    'tableId' => 'Meja tidak ditemukan.',
                ]);
            }

            $customerName = trim((string) $data->customerName);

            return $this->createOrder->handle(new CreateOrderData(
                items: $data->items,
                status: OrderStatus::Confirmed,
                tableId: $table->id,
                customerName: $customerName !== '' ? $customerName : 'Tamu Meja '.$table->code,
                notes: self::SOURCE->composeNotes($data->notes),
            ));
        });

        DB::afterCommit(fn () => OrderPlaced::dispatch($order->id, $order->table_id, self::SOURCE));

        return $order;
    }
}
