<?php

use App\Http\Controllers\Customer\CustomerBookingController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerMenuCatalogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['demo.login', 'auth', 'verified', 'role:customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {
        Route::get('dashboard', CustomerDashboardController::class)->name('dashboard');
        Route::get('menu/tables', [CustomerMenuCatalogController::class, 'tables'])->name('menus.tables');
        Route::get('menu', [CustomerMenuCatalogController::class, 'index'])->name('menus.index');
        Route::post('menu/cart', [CustomerMenuCatalogController::class, 'addToCart'])->name('menus.cart.store');
        Route::get('menu/cart', [CustomerMenuCatalogController::class, 'cart'])->name('menus.cart.index');
        Route::patch('menu/cart/{menuId}', [CustomerMenuCatalogController::class, 'updateCart'])->name('menus.cart.update');
        Route::delete('menu/cart/{menuId}', [CustomerMenuCatalogController::class, 'removeCart'])->name('menus.cart.destroy');
        Route::post('menu/cart/checkout', [CustomerMenuCatalogController::class, 'checkout'])->name('menus.cart.checkout');
        Route::get('booking', [CustomerBookingController::class, 'create'])->name('bookings.create');
        Route::post('booking', [CustomerBookingController::class, 'store'])->name('bookings.store');
    });
