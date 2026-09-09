<?php

use App\Http\Controllers\POS\PosOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['demo.login', 'auth', 'verified', 'role:superadmin|admin|cashier', 'can:orders.manage'])
    ->prefix('admin/pos')
    ->name('pos.')
    ->group(function () {
        Route::get('bills', [PosOrderController::class, 'bills'])->name('bills');

        // Kasir memesan lewat keranjang di halaman ini, bukan lewat form
        // create/edit — jadi hanya index yang ada. Namanya tetap
        // `pos.order.index`: dipakai navigasi, redirect login kasir, dan
        // kartu aksi di dashboard admin.
        Route::get('order', [PosOrderController::class, 'index'])->name('order.index');
    });
