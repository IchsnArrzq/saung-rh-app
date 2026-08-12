<?php

use App\Livewire\Customer\BookingForm;
use Illuminate\Support\Facades\Route;

// Advance table reservation with pre-ordered menu.
Route::get('booking', BookingForm::class)->name('bookings.create');
