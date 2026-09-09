<?php

namespace App\Livewire\Admin\Customers;

use App\Domains\Customer\QueryUseCases\GetCustomerListQueryUseCase;
use App\Domains\Customer\UseCases\DeleteCustomerUseCase;
use App\Models\Customer;
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
        $this->authorize('viewAny', Customer::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(string $id, DeleteCustomerUseCase $deleteCustomer): void
    {
        // Baris ditarik dulu supaya policy menilai pelanggan yang benar-benar
        // dihapus; use case hanya menerima id.
        $this->authorize('delete', Customer::query()->findOrFail($id));

        $deleteCustomer->handle($id);

        session()->flash('success', 'Pelanggan berhasil dihapus.');
    }

    public function render(GetCustomerListQueryUseCase $customerList): View
    {
        return view('livewire.admin.customers.table', [
            'customers' => $customerList->handle($this->search),
        ]);
    }
}
