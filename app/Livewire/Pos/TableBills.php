<?php

namespace App\Livewire\Pos;

use App\Domains\Order\QueryUseCases\GetOpenBillsQueryUseCase;
use App\Domains\Order\UseCases\SettleBillUseCase;
use App\Domains\Payment\Enums\PaymentMethod;
use App\Domains\System\QueryUseCases\GetPaymentAccountsQueryUseCase;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
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
        // `can:orders.manage` di rute menjaga *halamannya*, bukan method ini:
        // Livewire memanggilnya lewat POST /livewire/update, yang tidak melewati
        // middleware rute POS sama sekali. Menyembunyikan tombolnya di Blade juga
        // kosmetik. Jadi pelunasan diadili di sini, tepat sebelum uang dicatat.
        //
        // Abilitynya 'create' Payment — sama dengan yang menjaga aksi identik di
        // layar Order admin, dan memang itu yang dilakukan pelunasan.
        $this->authorize('create', Payment::class);

        // `payOrderId` ikut dikirim browser, jadi bisa saja kosong saat method
        // ini dipanggil — tanpa penjaga ini id kosong sampai ke query dan
        // menjawab 500, bukan pesan yang bisa dibaca kasir.
        if (blank($this->payOrderId)) {
            $this->addError('settle', 'Pilih tagihan yang mau ditutup terlebih dahulu.');

            return;
        }

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

    public function render(GetOpenBillsQueryUseCase $openBills, GetPaymentAccountsQueryUseCase $paymentAccounts)
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
            // Rekening tujuan hanya dibaca selagi modalnya terbuka — daftar
            // tagihan tidak perlu membayar query tambahan tiap ketikan pencarian.
            'accounts' => $payBill ? $paymentAccounts->forMethod($this->method) : new Collection,
            'accountExpected' => $payBill !== null && $paymentAccounts->expectsAccount($this->method),
        ]);
    }
}
