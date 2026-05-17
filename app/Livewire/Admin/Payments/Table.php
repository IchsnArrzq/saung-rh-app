<?php

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
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

    public function render(): View
    {
        $search = trim($this->search);

        $payments = Payment::query()
            ->with('order')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('method', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%')
                        ->orWhere('reference', 'like', '%'.$search.'%')
                        ->orWhereHas('order', fn (Builder $order) => $order->where('order_number', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate(12);

        return view('livewire.admin.payments.table', [
            'payments' => $payments,
        ]);
    }
}

