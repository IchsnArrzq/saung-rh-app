<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
| Pola otorisasi: lihat routes/admin/menus.php dan AGENTS.md § Authorization.
|
| Dashboard tidak punya model sendiri, jadi gerbangnya izin fitur — bukan
| policy. Kasir memegang `dashboard.view`, jadi halaman ini tetap terbuka
| untuknya.
*/

Route::get('dashboard', DashboardController::class)
    ->name('dashboard')
    ->can('dashboard.view');
