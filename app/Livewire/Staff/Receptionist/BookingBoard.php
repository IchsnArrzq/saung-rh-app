<?php

namespace App\Livewire\Staff\Receptionist;

use App\Models\Reservation;
use App\Services\Reservations\ReservationDepositService;
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
     * Reservation currently targeted by the deposit form (null = closed).
     */
    public ?string $depositFor = null;

    public string $depositAmount = '';

    public string $depositMethod = 'transfer';

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

        $reservation = Reservation::query()->with('table')->findOrFail($id);
        $reservation->status = $status;

        // Keep the table lock in sync with the booking lifecycle.
        match ($status) {
            'confirmed' => $this->onConfirmed($reservation),
            'seated' => $this->onSeated($reservation),
            'cancelled' => $this->onCancelled($reservation),
            default => null,
        };

        $reservation->save();

        session()->flash('success', "Reservasi {$reservation->customer_name} → ".self::ACTIONS[$status].'.');
    }

    public function openDeposit(string $id): void
    {
        $reservation = Reservation::query()->findOrFail($id);
        $this->depositFor = $reservation->id;
        $this->depositAmount = (string) ($reservation->deposit_amount ?? config('reservations.default_deposit_amount'));
        $this->depositMethod = 'transfer';
    }

    public function closeDeposit(): void
    {
        $this->reset(['depositFor', 'depositAmount', 'depositMethod']);
    }

    public function saveDeposit(ReservationDepositService $service): void
    {
        $this->validate([
            'depositAmount' => ['required', 'numeric', 'min:1'],
            'depositMethod' => ['required', 'in:cash,qris,debit_card,credit_card,transfer,ewallet'],
        ]);

        $reservation = Reservation::query()->with('table')->findOrFail($this->depositFor);

        $service->record(
            $reservation,
            (float) $this->depositAmount,
            $this->depositMethod,
            verifiedBy: auth()->user(),
        );

        session()->flash('success', "DP Rp ".number_format((float) $this->depositAmount, 0, ',', '.')." dicatat untuk {$reservation->customer_name}.");

        $this->closeDeposit();
    }

    private function onConfirmed(Reservation $reservation): void
    {
        $reservation->hold_until = null;
        $reservation->lockTable();
    }

    private function onSeated(Reservation $reservation): void
    {
        if ($occupied = \App\Models\TableStatus::query()->where('key', 'occupied')->first()) {
            $reservation->table?->update(['table_status_id' => $occupied->id]);
        }
    }

    private function onCancelled(Reservation $reservation): void
    {
        $reservation->released_at = now();
        $reservation->release_reason = 'manual';
        $reservation->releaseTable();
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
