<?php

use App\Http\Controllers\POS\PosOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('pos')
    ->name('pos.')
    ->group(function () {
        Route::resource('order', PosOrderController::class);
});
