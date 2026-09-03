<?php

namespace App\Domains\Payment\Actions;

use App\Domains\Payment\Enums\PaymentMethod;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Repositories\PaymentRepository;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Writes one settled payment row against an order.
 *
 * Earns its own Action because two separate flows need an identical row: the
 * cashier settling an open bill (SettleBillUseCase) and the POS taking the
 * money upfront (PlacePosOrderUseCase). Drift between the two — a missing
 * `paid_at`, a status that is not `paid` — would silently break automatic stock
 * deduction, which PaymentObserver drives off exactly those columns.
 */
class RecordPaymentAction
{
    public function __construct(private readonly PaymentRepository $payments) {}

    /**
     * @param  string  $referencePrefix  Short tag identifying the flow, e.g. POS or BILL.
     */
    public function handle(
        Order $order,
        PaymentMethod $method,
        float $amount,
        string $referencePrefix,
        string $notes,
    ): Payment {
        return $this->payments->create([
            'order_id' => $order->id,
            'method' => $method->value,
            'type' => 'full',
            'status' => PaymentStatus::Paid->value,
            'amount' => $amount,
            'reference' => $referencePrefix.'-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
            'verified_by' => Auth::id(),
            'notes' => $notes,
            'paid_at' => now(),
        ]);
    }
}
