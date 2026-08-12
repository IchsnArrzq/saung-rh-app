<?php

namespace App\Livewire\Admin\Orders;

use App\Domains\Order\QueryUseCases\GetOrderListQueryUseCase;
use App\Domains\Order\Services\OrderBillingService;
use App\Domains\Order\UseCases\DeleteOrderUseCase;
use App\Domains\Order\UseCases\SettleBillUseCase;
use App\Domains\Payment\Enums\PaymentMethod;
use App\Domains\Payment\Enums\PaymentStatus;
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

    public function delete(string $id, DeleteOrderUseCase $deleteOrder): void
    {
        $deleteOrder->handle($id);

        session()->flash('success', 'Order berhasil dihapus.');
    }

    public function showDetail(string $id, GetOrderListQueryUseCase $orderList, OrderBillingService $billing): void
    {
        $order = $orderList->detail($id);

        if (! $order) {
            return;
        }

        $paidTotal = $billing->paidAmount($order);

        $this->selectedOrder = [
            'id' => (string) $order->id,
            'order_number' => (string) $order->order_number,
            'customer_name' => (string) ($order->customer_name ?? 'Walk-in Customer'),
            'table' => (string) ($order->table?->code ?? '-'),
            'cashier' => (string) ($order->cashier?->name ?? '-'),
            'ordered_at' => (string) ($order->ordered_at?->format('d M Y H:i') ?? '-'),
            'status' => $order->status->label(),
            'notes' => (string) ($order->notes ?? ''),
            'subtotal' => (float) $order->subtotal,
            'discount' => (float) $order->discount,
            'tax' => (float) $order->tax,
            'total' => (float) $order->total,
            'paid_total' => $paidTotal,
            'remaining_total' => $billing->outstanding($order),
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
                    'method' => PaymentMethod::tryFrom((string) $payment->method)?->label() ?? (string) $payment->method,
                    'status' => PaymentStatus::tryFrom((string) $payment->status)?->label() ?? (string) $payment->status,
                    'amount' => (float) $payment->amount,
                    'paid_at' => (string) ($payment->paid_at?->format('d M Y H:i') ?? '-'),
                    'reference' => (string) ($payment->reference ?? '-'),
                ])
                ->values()
                ->all(),
        ];

        $this->dispatch('open-modal', 'order-detail-modal');
    }

    /**
     * Cash-settles the remaining balance. Shares SettleBillUseCase with the POS
     * bill screen, so an order paid from here also frees its table — previously
     * this button marked the order paid and left the table locked.
     */
    public function createPayment(string $id, SettleBillUseCase $settleBill): void
    {
        // A rejected settle (already paid, wrong status) surfaces as a
        // ValidationException, which Livewire renders through $errors.
        $payment = $settleBill->handle($id, PaymentMethod::Cash->value);

        session()->flash('success', 'Payment order '.$payment->order->order_number.' berhasil dibuat.');
    }

    public function render(GetOrderListQueryUseCase $orderList): View
    {
        return view('livewire.admin.orders.table', [
            'orders' => $orderList->handle($this->search),
        ]);
    }
}
