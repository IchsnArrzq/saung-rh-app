<?php

namespace App\Observers;

use App\Domains\Inventory\UseCases\DeductStockForPaymentUseCase;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Models\Payment;

/**
 * Deducts ingredient stock the moment money is actually received. Lives at the
 * infrastructure layer so no Action or UseCase has to remember to do it
 * (AGENTS.md § Audit Log).
 */
class PaymentObserver
{
    public function __construct(private readonly DeductStockForPaymentUseCase $deductStock) {}

    public function updated(Payment $payment): void
    {
        // Kurangi stok hanya ketika status berubah menjadi 'paid'
        if ($payment->wasChanged('status') && $this->isSettled($payment)) {
            $this->deductStock->handle($payment);
        }
    }

    public function created(Payment $payment): void
    {
        if ($this->isSettled($payment)) {
            $this->deductStock->handle($payment);
        }
    }

    private function isSettled(Payment $payment): bool
    {
        return PaymentStatus::tryFrom((string) $payment->status)?->isSettled() === true;
    }
}
