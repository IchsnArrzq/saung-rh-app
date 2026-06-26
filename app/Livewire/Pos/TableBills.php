<?php

namespace App\Livewire\Pos;

use App\Models\Order;
use App\Services\Admin\PaymentService;
use App\Services\Pos\BillingService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class TableBills extends Component
{
    public string $search = '';

    public ?string $payOrderId = null;

    public string $method = 'cash';

    public function openSettle(string $orderId): void
    {
        $this->resetErrorBag();
        $this->payOrderId = $orderId;
        $this->method = 'cash';
        $this->dispatch('open-modal', 'settle-bill-modal');
    }

    public function closeSettle(): void
    {
        $this->payOrderId = null;
        $this->dispatch('close-modal', 'settle-bill-modal');
    }

    public function settle(BillingService $billing): void
    {
        $order = Order::query()->with('payments')->find($this->payOrderId);

        if (! $order) {
            $this->addError('settle', 'Order tidak ditemukan.');

            return;
        }

        try {
            $payment = $billing->settle($order, $this->method);
        } catch (ValidationException $e) {
            $this->addError('settle', $e->validator->errors()->first());

            return;
        }

        $this->closeSettle();
        session()->flash('success', 'Tagihan '.$order->order_number.' lunas — Rp '.number_format((float) $payment->amount, 0, ',', '.').'.');
    }

    public function render(BillingService $billing)
    {
        $bills = $billing->openBills($this->search);

        $payBill = $this->payOrderId
            ? $bills->firstWhere('id', $this->payOrderId)
            : null;

        return view('livewire.pos.table-bills', [
            'bills' => $bills,
            'totalOutstanding' => $bills->sum('outstanding'),
            'methods' => PaymentService::METHOD_OPTIONS,
            'payBill' => $payBill,
        ]);
    }
}
