<?php

use App\Http\Controllers\POS\PosOrderController;
use Illuminate\Support\Facades\Route;

// Dijaga `can:orders.manage` saja. Gerbang `role:superadmin|admin|cashier` yang
// dulu mendampinginya dilepas: pemegang orders.manage memang tepat ketiga role
// itu, dan role baru dari layar Peran & hak akses kini bisa diberi akses kasir.
Route::middleware(['demo.login', 'auth', 'verified', 'can:orders.manage'])
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
