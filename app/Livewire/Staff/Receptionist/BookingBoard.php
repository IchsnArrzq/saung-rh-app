<?php

namespace App\Livewire\Staff\Receptionist;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BookingBoard extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    /**
     * Allowed status transitions a receptionist can apply from the board.
     */
    public const ACTIONS = [
        'confirmed' => 'Konfirmasi',
        'seated' => 'Check-in',
        'cancelled' => 'Batalkan',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function setStatus(string $id, string $status): void
    {
        if (! array_key_exists($status, self::ACTIONS)) {
            return;
        }

        $reservation = Reservation::query()->findOrFail($id);
        $reservation->update(['status' => $status]);

        session()->flash('success', "Reservasi {$reservation->customer_name} → ".self::ACTIONS[$status].'.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $reservations = Reservation::query()
            ->with('table')
            ->withCount('items')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhereHas('table', fn (Builder $table) => $table->where('code', 'like', '%'.$search.'%'));
                });
            })
            ->when($this->statusFilter !== 'all', fn (Builder $query) => $query->where('status', $this->statusFilter))
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'confirmed' THEN 1 ELSE 2 END")
            ->orderBy('reservation_at')
            ->paginate(12);

        $counts = Reservation::query()
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        return view('livewire.staff.receptionist.booking-board', [
            'reservations' => $reservations,
            'counts' => $counts,
            'todayCount' => Reservation::query()->whereDate('reservation_at', today())->count(),
        ]);
    }
}
