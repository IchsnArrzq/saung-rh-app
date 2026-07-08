<?php

namespace App\Livewire\Admin\Sales;

use App\Models\Sale;
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
        $sale = Sale::query()->findOrFail($id);

        if ($sale->isPosted()) {
            session()->flash('error', 'Penjualan yang sudah diposting tidak bisa dihapus.');

            return;
        }

        $sale->delete();

        session()->flash('success', 'Draft penjualan berhasil dihapus.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $sales = Sale::query()
            ->with('customer')
            ->withCount('items')
            ->when($this->statusFilter !== '', fn (Builder $q) => $q->where('status', $this->statusFilter))
            ->when($search !== '', function (Builder $q) use ($search): void {
                $q->where(function (Builder $inner) use ($search): void {
                    $inner->where('code', 'like', '%'.$search.'%')
                        ->orWhere('notes', 'like', '%'.$search.'%')
                        ->orWhereHas('customer', fn (Builder $c) => $c->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->latest('sale_date')
            ->latest()
            ->paginate(15);

        return view('livewire.admin.sales.table', [
            'sales' => $sales,
        ]);
    }
}
