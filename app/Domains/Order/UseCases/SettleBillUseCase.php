<?php

namespace App\Domains\Order\UseCases;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Services\OrderBillingService;
use App\Models\Order;
use App\Models\Payment;
use App\Domains\Payment\Enums\PaymentMethod;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Table\UseCases\ReleaseTableUseCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Settles the remaining balance of one order and closes the bill.
 *
 * Self-ordered tickets (QR guest, customer portal, waiter) arrive with no
 * payment attached; this is the cashier's way to take the money regardless of
 * who created the order.
 *
 * @todo Fase D — release the table by dispatching an event the Table domain
 *       listens to, instead of calling TableTurnoverService across the boundary
 *       (ARCHITECTURE.md § Domain Dependencies).
 */
class SettleBillUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly OrderBillingService $billing,
        private readonly ReleaseTableUseCase $releaseTable,
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

        if (! OrderStatus::from($order->status)->canTransitionTo(OrderStatus::Paid)) {
            throw ValidationException::withMessages([
                'settle' => 'Pesanan dengan status ini tidak bisa dilunasi.',
            ]);
        }

        return DB::transaction(function () use ($order, $method, $outstanding): Payment {
            $payment = $order->payments()->create([
                'method' => $method,
                'type' => 'full',
                'status' => PaymentStatus::Paid->value,
                'amount' => $outstanding,
                'reference' => 'BILL-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
                'verified_by' => Auth::id(),
                'notes' => 'Pelunasan tagihan meja via kasir.',
                'paid_at' => now(),
            ]);

            $this->orders->update($order, ['status' => OrderStatus::Paid->value]);

            // Free the table only once every bill on it is settled — a party can
            // run several order rounds. The just-paid order is already excluded
            // by the repository's open-bill filter.
            $table = $order->table;
            if ($table && ! $this->billing->anyOutstanding($this->orders->openOrdersForTable($table->id))) {
                $this->releaseTable->handle($table);
            }

            return $payment;
        });
    }
}
