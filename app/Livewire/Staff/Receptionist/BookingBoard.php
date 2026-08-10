<?php

namespace App\Livewire\Staff\Receptionist;

use App\Domains\Reservation\Enums\ReservationStatus;
use App\Domains\Reservation\QueryUseCases\GetReservationListQueryUseCase;
use App\Models\Reservation;
use App\Domains\Payment\Enums\PaymentMethod;
use App\Domains\Reservation\Repositories\ReservationRepositoryInterface;
use App\Domains\Reservation\UseCases\RecordReservationDepositUseCase;
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
        ReservationStatus::Confirmed->value => 'Konfirmasi',
        ReservationStatus::Seated->value => 'Check-in',
        ReservationStatus::Cancelled->value => 'Batalkan',
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
        match (ReservationStatus::tryFrom($status)) {
            ReservationStatus::Confirmed => $this->onConfirmed($reservation),
            ReservationStatus::Seated => $this->onSeated($reservation),
            ReservationStatus::Cancelled => $this->onCancelled($reservation),
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

    public function saveDeposit(RecordReservationDepositUseCase $recordDeposit, ReservationRepositoryInterface $reservations): void
    {
        $this->validate([
            'depositAmount' => ['required', 'numeric', 'min:1'],
            'depositMethod' => ['required', 'in:'.implode(',', PaymentMethod::values())],
        ]);

        $reservation = $reservations->findWithTable((string) $this->depositFor);

        if (! $reservation) {
            $this->addError('depositFor', 'Reservasi tidak ditemukan.');

            return;
        }

        $recordDeposit->handle(
            $reservation,
            (float) $this->depositAmount,
            PaymentMethod::from($this->depositMethod),
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
        $reservation->table?->update([
            'status' => \App\Domains\Table\Enums\TableStatus::Occupied->value,
        ]);
    }

    private function onCancelled(Reservation $reservation): void
    {
        $reservation->released_at = now();
        $reservation->release_reason = 'manual';
        $reservation->releaseTable();
    }

    public function render(GetReservationListQueryUseCase $reservationList): View
    {
        $search = trim($this->search);

        return view('livewire.staff.receptionist.booking-board', [
            'reservations' => $reservationList->forBoard($search, $this->statusFilter),
            'counts' => $reservationList->countsByStatus(),
            'todayCount' => $reservationList->countToday(),
        ]);
    }
}
