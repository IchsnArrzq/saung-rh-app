<?php

use App\Http\Controllers\POS\PosOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:superadmin|admin|cashier', 'can:orders.manage']) 
    ->prefix('pos')
    ->name('pos.')
    ->group(function () {
        Route::resource('order', PosOrderController::class);
});
