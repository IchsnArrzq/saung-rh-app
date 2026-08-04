<?php

namespace App\Observers;

use App\Domains\Payment\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\Admin\InventoryService;

/**
 * Deducts ingredient stock the moment money is actually received. Lives at the
 * infrastructure layer so no Action or UseCase has to remember to do it
 * (AGENTS.md § Audit Log).
 */
class PaymentObserver
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    public function updated(Payment $payment): void
    {
        // Kurangi stok hanya ketika status berubah menjadi 'paid'
        if ($payment->wasChanged('status') && $this->isSettled($payment)) {
            $this->inventoryService->deductFromPayment($payment);
        }
    }

    public function created(Payment $payment): void
    {
        if ($this->isSettled($payment)) {
            $this->inventoryService->deductFromPayment($payment);
        }
    }

    private function isSettled(Payment $payment): bool
    {
        return PaymentStatus::tryFrom((string) $payment->status)?->isSettled() === true;
    }
}
