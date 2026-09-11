<?php

use App\Http\Controllers\KDS\KdsController;
use Illuminate\Support\Facades\Route;

// Role bawaan lolos lewat namanya persis seperti dulu; role baru dari layar
// Peran & hak akses lolos lewat permission `kitchen.view`.
Route::middleware(['web', 'demo.login', 'auth', 'role_or_permission:superadmin|admin|chef|receptionist|kitchen.view'])
    ->prefix('admin/kds')
    ->name('kds.')
    ->group(function () {
        Route::get('/', [KdsController::class, 'index'])->name('index');
    });
