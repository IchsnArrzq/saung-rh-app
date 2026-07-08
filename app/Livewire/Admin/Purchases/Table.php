<?php

namespace App\Livewire\Admin\Purchases;

use App\Models\Purchase;
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

    #[Url(as: 'status', except: '')]
    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(string $id): void
    {
        $purchase = Purchase::query()->findOrFail($id);

        if ($purchase->isPosted()) {
            session()->flash('error', 'Pembelian yang sudah diposting tidak bisa dihapus.');

            return;
        }

        $purchase->delete();

        session()->flash('success', 'Draft pembelian berhasil dihapus.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $purchases = Purchase::query()
            ->with('supplier')
            ->withCount('items')
            ->when($this->statusFilter !== '', fn (Builder $q) => $q->where('status', $this->statusFilter))
            ->when($search !== '', function (Builder $q) use ($search): void {
                $q->where(function (Builder $inner) use ($search): void {
                    $inner->where('code', 'like', '%'.$search.'%')
                        ->orWhere('notes', 'like', '%'.$search.'%')
                        ->orWhereHas('supplier', fn (Builder $s) => $s->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->latest('purchase_date')
            ->latest()
            ->paginate(15);

        return view('livewire.admin.purchases.table', [
            'purchases' => $purchases,
        ]);
    }
}
