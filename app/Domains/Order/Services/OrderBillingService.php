<?php

namespace App\Domains\Order\Services;

use App\Domains\Payment\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Collection;

/**
 * Money maths over already-loaded Order models. Deliberately query-free —
 * fetching is the Repository's job (AGENTS.md § Database Rules).
 *
 * Single implementation forever, so it is injected as a concrete class with no
 * interface (AGENTS.md § Service).
 */
class OrderBillingService
{
    public function paidAmount(Order $order): float
    {
        return (float) $order->payments
            ->where('status', PaymentStatus::Paid->value)
            ->sum('amount');
    }

    public function outstanding(Order $order): float
    {
        return max(0.0, (float) $order->total - $this->paidAmount($order));
    }

    /**
     * Whether any of the given orders still owes money. Used to decide when a
     * table is finally free — a party can run several rounds on one table.
     *
     * @param  Collection<int, Order>  $orders
     */
    public function anyOutstanding(Collection $orders): bool
    {
        return $orders->contains(fn (Order $order) => $this->outstanding($order) > 0.0);
    }

    /**
     * Flattened bill view for the cashier worklist.
     *
     * @return array<string, mixed>
     */
    public function summarize(Order $order): array
    {
        $paid = $this->paidAmount($order);
        $total = (float) $order->total;

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'table_code' => $order->table?->code,
            'customer_name' => $order->customer_name,
            'status' => $order->status,
            'source' => $this->source($order),
            'ordered_at' => $order->ordered_at,
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->menu_name_snapshot,
                'qty' => (int) $item->qty,
                'line_total' => (float) $item->line_total,
            ])->all(),
            'total' => $total,
            'paid' => $paid,
            'outstanding' => max(0.0, $total - $paid),
        ];
    }

    private function source(Order $order): string
    {
        $notes = (string) $order->notes;

        return match (true) {
            str_contains($notes, 'QR') => 'QR',
            str_contains($notes, 'POS') => 'POS',
            default => 'App',
        };
    }
}
