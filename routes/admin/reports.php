<?php

use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;

/*
| Pola otorisasi: lihat routes/admin/menus.php dan AGENTS.md § Authorization.
|
| Laporan menggabungkan banyak model, jadi gerbangnya izin fitur `reports.view`
| — dipegang superadmin, admin, dan manager, tidak oleh kasir.
*/

Route::get('reports', [ReportController::class, 'index'])
    ->name('reports.index')
    ->can('reports.view');
