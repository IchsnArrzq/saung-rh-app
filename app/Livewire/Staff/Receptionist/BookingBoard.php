<?php

namespace App\Livewire\Staff\Receptionist;

use App\Domains\Payment\Enums\PaymentMethod;
use App\Domains\Reservation\Enums\ReservationStatus;
use App\Domains\Reservation\QueryUseCases\GetReservationListQueryUseCase;
use App\Domains\Reservation\Repositories\ReservationRepository;
use App\Domains\Reservation\UseCases\RecordReservationDepositUseCase;
use App\Domains\System\QueryUseCases\GetPaymentAccountsQueryUseCase;
use App\Domains\Table\Enums\TableStatus;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Collection;
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

        // Diadili di sini, bukan cuma di rute: Livewire memanggil method ini
        // lewat POST /livewire/update, yang tidak melewati `can:reservations.manage`
        // milik rute resepsionis.
        $this->authorize('update', $reservation);

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

        // Membuka form DP sudah menyingkap nomor rekening bisnis, jadi diadili
        // di sini juga — bukan hanya saat menyimpannya.
        $this->authorize('update', $reservation);

        $this->depositFor = $reservation->id;
        $this->depositAmount = (string) ($reservation->deposit_amount ?? config('reservations.default_deposit_amount'));
        $this->depositMethod = 'transfer';
    }

    public function closeDeposit(): void
    {
        $this->reset(['depositFor', 'depositAmount', 'depositMethod']);
    }

    public function saveDeposit(RecordReservationDepositUseCase $recordDeposit, ReservationRepository $reservations): void
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

        // Diulang di sini: openDeposit() adalah request tersendiri, dan
        // `depositFor` ikut dikirim browser — jangan percaya sisa state-nya.
        $this->authorize('update', $reservation);

        $recordDeposit->handle(
            $reservation,
            (float) $this->depositAmount,
            PaymentMethod::from($this->depositMethod),
            verifiedBy: auth()->user(),
        );

        session()->flash('success', 'DP Rp '.number_format((float) $this->depositAmount, 0, ',', '.')." dicatat untuk {$reservation->customer_name}.");

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
            'status' => TableStatus::Occupied->value,
        ]);
    }

    private function onCancelled(Reservation $reservation): void
    {
        $reservation->released_at = now();
        $reservation->release_reason = 'manual';
        $reservation->releaseTable();
    }

    public function render(
        GetReservationListQueryUseCase $reservationList,
        GetPaymentAccountsQueryUseCase $paymentAccounts,
    ): View {
        $search = trim($this->search);

        return view('livewire.staff.receptionist.booking-board', [
            'reservations' => $reservationList->forBoard($search, $this->statusFilter),
            'counts' => $reservationList->countsByStatus(),
            'todayCount' => $reservationList->countToday(),
            'methodOptions' => PaymentMethod::options(),
            // Rekening tujuan hanya dibaca selagi form DP terbuka; papan
            // reservasi sendiri tidak memerlukannya.
            'accounts' => $this->depositFor
                ? $paymentAccounts->forMethod($this->depositMethod)
                : new Collection,
            'accountExpected' => $this->depositFor !== null
                && $paymentAccounts->expectsAccount($this->depositMethod),
        ]);
    }
}
