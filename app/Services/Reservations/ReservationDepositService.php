<?php

namespace App\Services\Reservations;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReservationDepositService
{
    /**
     * Record a confirmed down payment for a reservation: persist the payment,
     * stamp the reservation as paid/confirmed and lock its table.
     */
    public function record(
        Reservation $reservation,
        float $amount,
        string $method = 'transfer',
        ?string $proofImagePath = null,
        ?User $verifiedBy = null,
    ): Payment {
        return DB::transaction(function () use ($reservation, $amount, $method, $proofImagePath, $verifiedBy): Payment {
            $payment = $reservation->payments()->create([
                'method' => $method,
                'type' => 'deposit',
                'status' => 'paid',
                'amount' => $amount,
                'proof_image_path' => $proofImagePath,
                'verified_by' => $verifiedBy?->id,
                'paid_at' => now(),
            ]);

            $reservation->forceFill([
                'deposit_amount' => $amount,
                'deposit_paid_at' => now(),
                'hold_until' => null,
                'status' => $reservation->status === 'pending' ? 'confirmed' : $reservation->status,
            ])->save();

            $reservation->refresh()->lockTable();

            return $payment;
        });
    }
}
