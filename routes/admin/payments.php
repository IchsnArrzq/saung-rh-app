<?php

use App\Http\Controllers\Admin\PaymentController;
use Illuminate\Support\Facades\Route;

Route::resource('payments', PaymentController::class)->except('show');

Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
Route::get('payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
