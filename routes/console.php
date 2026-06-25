<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Free up tables held by reservations that lapsed without a deposit or check-in.
Schedule::command('reservations:release-expired')
    ->everyFifteenMinutes()
    ->withoutOverlapping();
