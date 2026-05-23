<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KDS\KdsController;

Route::middleware(['web', 'auth', 'role:superadmin|admin'])
    ->prefix('kds')
    ->name('kds.')
    ->group(function () {
        Route::get('/', [KdsController::class, 'index'])->name('index');
    });
