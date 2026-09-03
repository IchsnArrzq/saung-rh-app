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
        $this->authorize('viewAny', Payment::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(string $id, GetPaymentListQueryUseCase $paymentList): void
    {
        // Lewat QueryUseCase, bukan Payment::query() langsung dari Livewire —
        // AGENTS.md § Database Rules.
        $payment = $paymentList->find($id);

        if (! $payment) {
            return;
        }

        $this->authorize('delete', $payment);

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
