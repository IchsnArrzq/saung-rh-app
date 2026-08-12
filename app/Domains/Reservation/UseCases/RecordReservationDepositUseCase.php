<?php

namespace App\Domains\Reservation\UseCases;

use App\Domains\Payment\Enums\PaymentMethod;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Repositories\PaymentRepositoryInterface;
use App\Domains\Reservation\Enums\ReservationStatus;
use App\Domains\Reservation\Repositories\ReservationRepositoryInterface;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Books the down payment a guest paid to hold their table: records the payment,
 * confirms the booking and locks the table.
 *
 * All three in one transaction — a deposit taken without the table being locked
 * means the table can be sold twice.
 *
 * Replaces App\Services\Reservations\ReservationDepositService.
 */
class RecordReservationDepositUseCase
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservations,
        private readonly PaymentRepositoryInterface $payments,
    ) {}

    public function handle(
        Reservation $reservation,
        float $amount,
        PaymentMethod $method = PaymentMethod::Transfer,
        ?string $proofImagePath = null,
        ?User $verifiedBy = null,
    ): Payment {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'deposit' => 'Nominal deposit harus lebih dari nol.',
            ]);
        }

        return DB::transaction(function () use ($reservation, $amount, $method, $proofImagePath, $verifiedBy): Payment {
            $payment = $this->payments->create([
                'reservation_id' => $reservation->id,
                'method' => $method->value,
                'type' => 'deposit',
                'status' => PaymentStatus::Paid->value,
                'amount' => $amount,
                'proof_image_path' => $proofImagePath,
                'verified_by' => $verifiedBy?->id,
                'paid_at' => now(),
            ]);

            $this->reservations->update($reservation, [
                'deposit_amount' => $amount,
                'deposit_paid_at' => now(),
                // The hold has been honoured, so the auto-release job must stop
                // watching this booking.
                'hold_until' => null,
                'status' => $reservation->status === ReservationStatus::Pending->value
                    ? ReservationStatus::Confirmed->value
                    : $reservation->status,
            ]);

            $reservation->refresh()->lockTable();

            return $payment;
        });
    }
}
