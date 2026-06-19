<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $selectedOrder = null;

    public function mount(): void
    {
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(string $id): void
    {
        $order = Order::query()->findOrFail($id);
        $order->delete();

        session()->flash('success', 'Order berhasil dihapus.');
    }

    public function showDetail(string $id): void
    {
        $order = Order::query()
            ->with(['table', 'cashier', 'items.menu', 'payments'])
            ->findOrFail($id);

        $paidTotal = (float) $order->payments
            ->where('status', 'paid')
            ->sum('amount');

        $this->selectedOrder = [
            'id' => (string) $order->id,
            'order_number' => (string) $order->order_number,
            'customer_name' => (string) ($order->customer_name ?? 'Walk-in Customer'),
            'table' => (string) ($order->table?->code ?? '-'),
            'cashier' => (string) ($order->cashier?->name ?? '-'),
            'ordered_at' => (string) ($order->ordered_at?->format('d M Y H:i') ?? '-'),
            'status' => (string) str_replace('_', ' ', $order->status),
            'notes' => (string) ($order->notes ?? ''),
            'subtotal' => (float) $order->subtotal,
            'discount' => (float) $order->discount,
            'tax' => (float) $order->tax,
            'total' => (float) $order->total,
            'paid_total' => $paidTotal,
            'remaining_total' => max(0, (float) $order->total - $paidTotal),
            'items' => $order->items
                ->map(fn ($item): array => [
                    'name' => (string) $item->menu_name_snapshot,
                    'qty' => (int) $item->qty,
                    'price' => (float) $item->price,
                    'line_total' => (float) $item->line_total,
                    'notes' => (string) ($item->notes ?? ''),
                    'status' => (string) ($item->status ?? '-'),
                ])
                ->values()
                ->all(),
            'payments' => $order->payments
                ->map(fn ($payment): array => [
                    'method' => (string) str_replace('_', ' ', $payment->method),
                    'status' => (string) $payment->status,
                    'amount' => (float) $payment->amount,
                    'paid_at' => (string) ($payment->paid_at?->format('d M Y H:i') ?? '-'),
                    'reference' => (string) ($payment->reference ?? '-'),
                ])
                ->values()
                ->all(),
        ];

        $this->dispatch('open-modal', 'order-detail-modal');
    }

    public function createPayment(string $id): void
    {
        $order = Order::query()->with('payments')->findOrFail($id);

        $paidTotal = (float) $order->payments()
            ->where('status', 'paid')
            ->sum('amount');
        $remainingTotal = max(0, (float) $order->total - $paidTotal);

        if ($remainingTotal <= 0) {
            session()->flash('success', 'Order '.$order->order_number.' sudah lunas.');

            return;
        }

        Payment::query()->create([
            'order_id' => $order->id,
            'method' => 'cash',
            'type' => 'full',
            'status' => 'paid',
            'amount' => $remainingTotal,
            'reference' => 'PAY-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
            'notes' => 'Dibuat dari tombol payment order table.',
            'paid_at' => now(),
        ]);

        $order->update(['status' => 'paid']);

        session()->flash('success', 'Payment order '.$order->order_number.' berhasil dibuat.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $orders = Order::query()
            ->with('table')
            ->withCount('items')
            ->withSum(['payments as paid_total' => fn ($query) => $query->where('status', 'paid')], 'amount')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('order_number', 'like', '%'.$search.'%')
                        ->orWhere('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%')
                        ->orWhereHas('table', fn (Builder $table) => $table->where('code', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate(12);

        return view('livewire.admin.orders.table', [
            'orders' => $orders,
        ]);
    }
}
