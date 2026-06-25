<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Deposit / Down Payment (DP)
    |--------------------------------------------------------------------------
    |
    | Default down payment amount suggested when a receptionist records a
    | deposit for a reservation, and the window (in minutes) the table is held
    | while waiting for the deposit before the hold auto-expires.
    |
    */
    'default_deposit_amount' => env('RESERVATION_DEPOSIT_AMOUNT', 50000),

    'deposit_hold_minutes' => env('RESERVATION_HOLD_MINUTES', 120),

    /*
    |--------------------------------------------------------------------------
    | No-show grace period
    |--------------------------------------------------------------------------
    |
    | Minutes after the reserved time a confirmed booking is kept before it is
    | flagged as a no-show and the table lock is released.
    |
    */
    'no_show_grace_minutes' => env('RESERVATION_NO_SHOW_GRACE_MINUTES', 15),
];
