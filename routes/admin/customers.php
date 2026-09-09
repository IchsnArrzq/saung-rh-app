<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Models\Customer;
use Illuminate\Support\Facades\Route;

/*
| Pola otorisasi: lihat routes/admin/menus.php dan AGENTS.md § Authorization.
*/

// Contacts: Pelanggan (Customer)
Route::get('customers', [CustomerController::class, 'index'])
    ->name('customers.index')
    ->can('viewAny', Customer::class);

Route::get('customers/create', [CustomerController::class, 'create'])
    ->name('customers.create')
    ->can('create', Customer::class);

Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])
    ->name('customers.edit')
    ->can('update', 'customer');
