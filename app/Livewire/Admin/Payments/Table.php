<?php

namespace App\Livewire\Admin\Payments;

use App\Domains\Payment\QueryUseCases\GetPaymentListQueryUseCase;
use App\Models\Payment;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    public function mount(): void
    {
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(string $id): void
    {
        $payment = Payment::query()->findOrFail($id);
        $payment->delete();

        session()->flash('success', 'Pembayaran berhasil dihapus.');
    }

    public function render(GetPaymentListQueryUseCase $paymentList): View
    {
        return view('livewire.admin.payments.table', [
            'payments' => $paymentList->handle($this->search),
        ]);
    }
}

