<?php

use App\Http\Controllers\Admin\CustomerController;
use Illuminate\Support\Facades\Route;

// Contacts: Pelanggan (Customer)
Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
