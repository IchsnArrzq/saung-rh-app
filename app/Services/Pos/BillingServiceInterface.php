<?php

namespace App\Services\Pos;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use Illuminate\Support\Collection;

/**
 * Cashier billing for dine-in orders. Self-ordered tickets (QR guest, customer
 * portal, waiter) arrive with no payment; this lets the cashier settle the
 * outstanding balance and close the bill, regardless of who created the order.
 */
interface BillingServiceInterface
{
    /**
     * Orders that still owe money — the cashier's open-bills worklist.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function openBills(string $search = ''): Collection;

    /**
     * @return array<string, mixed>
     */
    public function summarize(Order $order): array;

    public function paidAmount(Order $order): float;

    public function outstanding(Order $order): float;

    /**
     * Settle the remaining balance of an order in one payment and close the bill.
     */
    public function settle(Order $order, string $method): Payment;

    /**
     * Whether a table still has any unpaid order on it.
     */
    public function tableHasOpenBills(Table $table): bool;
}
