<?php

namespace App\Livewire\Pos;

use App\Domains\Order\QueryUseCases\GetOpenBillsQueryUseCase;
use App\Domains\Order\UseCases\SettleBillUseCase;
use App\Domains\Payment\Enums\PaymentMethod;
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

    public function settle(SettleBillUseCase $settleBill): void
    {
        try {
            $payment = $settleBill->handle((string) $this->payOrderId, $this->method);
        } catch (ValidationException $e) {
            $this->addError('settle', $e->validator->errors()->first());

            return;
        }

        $orderNumber = $payment->order?->order_number ?? '';

        $this->closeSettle();
        session()->flash('success', 'Tagihan '.$orderNumber.' lunas — Rp '.number_format((float) $payment->amount, 0, ',', '.').'.');
    }

    public function render(GetOpenBillsQueryUseCase $openBills)
    {
        $bills = $openBills->handle($this->search);

        $payBill = $this->payOrderId
            ? $bills->firstWhere('id', $this->payOrderId)
            : null;

        return view('livewire.pos.table-bills', [
            'bills' => $bills,
            'totalOutstanding' => $bills->sum('outstanding'),
            'methods' => PaymentMethod::cases(),
            'payBill' => $payBill,
        ]);
    }
}
