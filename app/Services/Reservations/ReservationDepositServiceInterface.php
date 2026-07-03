<?php

namespace App\Services\Reservations;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;

interface ReservationDepositServiceInterface
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
    ): Payment;
}
