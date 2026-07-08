<?php

namespace App\Livewire\Admin\Customers;

use App\Models\Customer;
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(string $id): void
    {
        $customer = Customer::query()->findOrFail($id);
        $customer->delete();

        session()->flash('success', 'Pelanggan berhasil dihapus.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $customers = Customer::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.customers.table', [
            'customers' => $customers,
        ]);
    }
}
