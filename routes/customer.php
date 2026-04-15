<?php

use App\Http\Controllers\Customer\CustomerBookingController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerMenuCatalogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {
        Route::get('dashboard', CustomerDashboardController::class)->name('dashboard');
        Route::get('menu', CustomerMenuCatalogController::class)->name('menus.index');
        Route::get('booking', [CustomerBookingController::class, 'create'])->name('bookings.create');
        Route::post('booking', [CustomerBookingController::class, 'store'])->name('bookings.store');
    });
