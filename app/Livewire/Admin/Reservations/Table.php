<?php

namespace App\Livewire\Admin\Reservations;

use App\Models\Reservation;
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
        $this->authorize('viewAny', Reservation::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(string $id): void
    {
        $reservation = Reservation::query()->findOrFail($id);
        $this->authorize('delete', $reservation);
        $reservation->delete();

        session()->flash('success', 'Reservasi berhasil dihapus.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $reservations = Reservation::query()
            ->with('table')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%')
                        ->orWhereHas('table', fn (Builder $table) => $table->where('code', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate(12);

        return view('livewire.admin.reservations.table', [
            'reservations' => $reservations,
        ]);
    }
}

