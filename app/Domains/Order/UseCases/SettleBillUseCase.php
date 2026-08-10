<?php

namespace App\Domains\Order\UseCases;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Events\TableBillsCleared;
use App\Domains\Order\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Services\OrderBillingService;
use App\Domains\Payment\Actions\RecordPaymentAction;
use App\Domains\Payment\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Settles the remaining balance of one order and closes the bill.
 *
 * Self-ordered tickets (QR guest, customer portal, waiter) arrive with no
 * payment attached; this is the cashier's way to take the money regardless of
 * who created the order.
 *
 * Two cross-domain touches, handled differently on purpose:
 *  - the Payment row is written inline, because it *is* the settlement and the
 *    caller is handed it back;
 *  - freeing the table is a consequence, so it leaves as an event. Whether the
 *    table still owes anything is answered here — a party can run several
 *    rounds — and TableBillsCleared is only announced once it owes nothing.
 */
class SettleBillUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly OrderBillingService $billing,
        private readonly RecordPaymentAction $recordPayment,
    ) {}

    public function handle(string $orderId, string $method): Payment
    {
        if (! PaymentMethod::tryFrom($method)) {
            throw ValidationException::withMessages([
                'method' => 'Metode pembayaran tidak valid.',
            ]);
        }

        $order = $this->orders->find($orderId);

        if (! $order) {
            throw ValidationException::withMessages([
                'settle' => 'Order tidak ditemukan.',
            ]);
        }

        $order->loadMissing('payments', 'table');
        $outstanding = $this->billing->outstanding($order);

        if ($outstanding <= 0.0) {
            throw ValidationException::withMessages([
                'settle' => 'Tagihan ini sudah lunas.',
            ]);
        }

        if (! $order->status->canTransitionTo(OrderStatus::Paid)) {
            throw ValidationException::withMessages([
                'settle' => 'Pesanan dengan status ini tidak bisa dilunasi.',
            ]);
        }

        [$payment, $tableCleared] = DB::transaction(function () use ($order, $method, $outstanding): array {
            $payment = $this->recordPayment->handle(
                $order,
                PaymentMethod::from($method),
                $outstanding,
                'BILL',
                'Pelunasan tagihan meja via kasir.',
            );

            $this->orders->update($order, ['status' => OrderStatus::Paid->value]);

            // The just-paid order is already excluded by the repository's
            // open-bill filter, so this asks about everything *else* on the table.
            $tableCleared = $order->table
                && ! $this->billing->anyOutstanding($this->orders->openOrdersForTable($order->table->id));

            return [$payment, $tableCleared];
        });

        if ($tableCleared) {
            DB::afterCommit(fn () => TableBillsCleared::dispatch($order->table->id, $order->id));
        }

        return $payment;
    }
}
