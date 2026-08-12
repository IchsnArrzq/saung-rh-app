<?php

use App\Http\Controllers\Admin\OrderController;
use Illuminate\Support\Facades\Route;

Route::resource('orders', OrderController::class)->except('show');

Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
