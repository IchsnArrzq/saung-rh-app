<?php

use App\Livewire\Customer\BookingForm;
use App\Livewire\Customer\Dashboard;
use App\Livewire\Customer\MenuOrder;
use App\Livewire\Customer\TablePicker;
use Illuminate\Support\Facades\Route;

Route::middleware(['demo.login', 'auth', 'verified', 'role:customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {
        Route::get('dashboard', Dashboard::class)->name('dashboard');

        // Dine-in ordering (Livewire full-page): pick a table, then order.
        Route::get('menu/tables', TablePicker::class)->name('menus.tables');
        Route::get('menu', MenuOrder::class)->name('menus.index');

        // Advance table reservation with pre-ordered menu.
        Route::get('booking', BookingForm::class)->name('bookings.create');
    });
