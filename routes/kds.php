<?php

use App\Http\Controllers\KDS\KdsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'demo.login', 'auth', 'role:superadmin|admin|chef|receptionist'])
    ->prefix('admin/kds')
    ->name('kds.')
    ->group(function () {
        Route::get('/', [KdsController::class, 'index'])->name('index');
    });
