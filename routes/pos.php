<?php

use App\Http\Controllers\POS\PosOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['demo.login', 'auth', 'verified', 'role:superadmin|admin|cashier', 'can:orders.manage'])
    ->prefix('admin/pos')
    ->name('pos.')
    ->group(function () {
        Route::get('bills', [PosOrderController::class, 'bills'])->name('bills');
        Route::resource('order', PosOrderController::class);
    });
