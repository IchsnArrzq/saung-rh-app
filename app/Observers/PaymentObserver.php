<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\Admin\InventoryServiceInterface;

class PaymentObserver
{
    public function __construct(private readonly InventoryServiceInterface $inventoryService) {}

    public function updated(Payment $payment): void
    {
        // Kurangi stok hanya ketika status berubah menjadi 'paid'
        if ($payment->wasChanged('status') && $payment->status === 'paid') {
            $this->inventoryService->deductFromPayment($payment);
        }
    }

    public function created(Payment $payment): void
    {
        if ($payment->status === 'paid') {
            $this->inventoryService->deductFromPayment($payment);
        }
    }
}
