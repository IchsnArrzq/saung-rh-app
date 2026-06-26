<?php

namespace App\Services\Pos;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Services\Admin\PaymentService;
use App\Services\Tables\TableTurnoverService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Cashier billing for dine-in orders. Self-ordered tickets (QR guest, customer
 * portal, waiter) arrive with no payment; this lets the cashier settle the
 * outstanding balance and close the bill, regardless of who created the order.
 */
class BillingService
{
    public function __construct(private readonly TableTurnoverService $turnover) {}

    /**
     * Orders that still owe money — the cashier's open-bills worklist.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function openBills(string $search = ''): Collection
    {
        $search = trim($search);

        return Order::query()
            ->with(['table', 'items', 'payments'])
            ->whereNotIn('status', ['cancelled', 'paid'])
            ->where('total', '>', 0)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('order_number', 'like', '%'.$search.'%')
                        ->orWhere('customer_name', 'like', '%'.$search.'%')
                        ->orWhereHas('table', fn ($table) => $table->where('code', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('ordered_at')
            ->get()
            ->map(fn (Order $order) => $this->summarize($order))
            ->filter(fn (array $bill) => $bill['outstanding'] > 0.0)
            ->values();
    }

    /**
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
            'source' => str_contains((string) $order->notes, 'QR') ? 'QR' : (str_contains((string) $order->notes, 'POS') ? 'POS' : 'App'),
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

    public function paidAmount(Order $order): float
    {
        return (float) $order->payments
            ->where('status', 'paid')
            ->sum('amount');
    }

    public function outstanding(Order $order): float
    {
        return max(0.0, (float) $order->total - $this->paidAmount($order));
    }

    /**
     * Settle the remaining balance of an order in one payment and close the bill.
     */
    public function settle(Order $order, string $method): Payment
    {
        if (! in_array($method, PaymentService::METHOD_OPTIONS, true)) {
            throw ValidationException::withMessages([
                'method' => 'Metode pembayaran tidak valid.',
            ]);
        }

        $order->loadMissing('payments', 'table');
        $outstanding = $this->outstanding($order);

        if ($outstanding <= 0.0) {
            throw ValidationException::withMessages([
                'settle' => 'Tagihan ini sudah lunas.',
            ]);
        }

        return DB::transaction(function () use ($order, $method, $outstanding): Payment {
            $payment = $order->payments()->create([
                'method' => $method,
                'type' => 'full',
                'status' => 'paid',
                'amount' => $outstanding,
                'reference' => 'BILL-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
                'verified_by' => Auth::id(),
                'notes' => 'Pelunasan tagihan meja via kasir.',
                'paid_at' => now(),
            ]);

            $order->update(['status' => 'paid']);

            // Free the table only once every bill on it is settled (a party can
            // have multiple order rounds). Sends it to "cleaning" + closes the
            // QR session.
            $table = $order->table;
            if ($table && ! $this->tableHasOpenBills($table)) {
                $this->turnover->release($table);
            }

            return $payment;
        });
    }

    /**
     * Whether a table still has any unpaid order on it.
     */
    public function tableHasOpenBills(Table $table): bool
    {
        return Order::query()
            ->where('table_id', $table->id)
            ->whereNotIn('status', ['cancelled', 'paid'])
            ->where('total', '>', 0)
            ->with('payments')
            ->get()
            ->contains(fn (Order $order) => $this->outstanding($order) > 0.0);
    }
}
