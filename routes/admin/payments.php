<?php

use App\Http\Controllers\Admin\PaymentController;
use App\Models\Payment;
use Illuminate\Support\Facades\Route;

/*
| Pola otorisasi: lihat routes/admin/menus.php dan AGENTS.md § Authorization.
|
| `Route::resource` dilepas dengan alasan yang sama seperti pada orders.php.
*/

Route::get('payments', [PaymentController::class, 'index'])
    ->name('payments.index')
    ->can('viewAny', Payment::class);

Route::get('payments/create', [PaymentController::class, 'create'])
    ->name('payments.create')
    ->can('create', Payment::class);

Route::get('payments/{payment}/edit', [PaymentController::class, 'edit'])
    ->name('payments.edit')
    ->can('update', 'payment');
