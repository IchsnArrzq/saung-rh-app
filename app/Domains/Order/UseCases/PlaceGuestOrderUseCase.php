<?php

namespace App\Domains\Order\UseCases;

use App\Domains\Order\DTO\CreateOrderData;
use App\Domains\Order\DTO\PlaceGuestOrderData;
use App\Domains\Order\Enums\OrderSource;
use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Events\OrderPlaced;
use App\Domains\Table\Enums\TableSessionStatus;
use App\Domains\Table\Repositories\TableSessionRepository;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Dine-in ticket sent by a guest from the QR menu. No account, no payment —
 * the cashier settles the table later (SettleBillUseCase).
 *
 * Only a staff-approved, still-open table session may order, and the ticket
 * goes to that session's table. The session and table are read here but never
 * written: the Table domain reacts to OrderPlaced on its own.
 */
class PlaceGuestOrderUseCase
{
    private const SOURCE = OrderSource::DineInQr;

    public function __construct(
        private readonly CreateOrderUseCase $createOrder,
        private readonly TableSessionRepository $sessions,
    ) {}

    public function handle(PlaceGuestOrderData $data): Order
    {
        $order = DB::transaction(function () use ($data): Order {
            $session = $this->sessions->findInStatus($data->tableSessionId, [TableSessionStatus::Active->value]);

            if (! $session || ! $session->table) {
                throw ValidationException::withMessages([
                    'cart' => 'Sesi meja Anda belum dikonfirmasi kasir atau sudah berakhir. Scan QR di meja untuk mulai lagi.',
                ]);
            }

            $table = $session->table;
            $customerName = trim((string) ($data->customerName ?: $session->customer_name));

            return $this->createOrder->handle(new CreateOrderData(
                items: $data->items,
                status: OrderStatus::Confirmed,
                tableId: $table->id,
                customerName: $customerName !== '' ? $customerName : 'Tamu Meja '.$table->code,
                notes: self::SOURCE->composeNotes($data->notes),
                tableSessionId: $session->id,
            ));
        });

        DB::afterCommit(fn () => OrderPlaced::dispatch($order->id, $order->table_id, self::SOURCE));

        return $order;
    }
}
