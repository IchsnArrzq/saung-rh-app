<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Order::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(string $id): void
    {
        $order = Order::query()->findOrFail($id);
        $this->authorize('delete', $order);
        $order->delete();

        session()->flash('success', 'Order berhasil dihapus.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $orders = Order::query()
            ->with('table')
            ->withCount('items')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('order_number', 'like', '%'.$search.'%')
                        ->orWhere('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%')
                        ->orWhereHas('table', fn (Builder $table) => $table->where('code', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate(12);

        return view('livewire.admin.orders.table', [
            'orders' => $orders,
        ]);
    }
}

