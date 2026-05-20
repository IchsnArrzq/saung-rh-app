<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\CustomerUserController;
use App\Http\Controllers\Admin\MenuCategoryController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuStatusController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\TableCategoryController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\TableStatusController;
use App\Livewire\Admin\TableQrPage;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:superadmin|admin|cashier'])
    ->prefix('admin')
    ->group(function () {

        Route::view('dashboard', 'dashboard')->name('dashboard')->middleware('can:dashboard.view');

        Route::middleware('can:tables.manage')->group(function () {
            Route::get('tables/{table}/qr', TableQrPage::class)->name('tables.qr');
            Route::patch('tables/{table}/status', [TableController::class, 'updateStatus'])->name('tables.status');
            Route::resource('tables', TableController::class)->except('show');
            Route::resource('table-statuses', TableStatusController::class)->except('show')->parameters(['table-statuses' => 'tableStatus']);
            Route::resource('table-categories', TableCategoryController::class)->except('show')->parameters(['table-categories' => 'tableCategory']);
            Route::resource('reservations', ReservationController::class)->except('show');
        });

        Route::middleware('can:menus.manage')->group(function () {
            Route::resource('menus', MenuController::class)->except('show');
            Route::resource('menu-categories', MenuCategoryController::class)->except('show')->parameters(['menu-categories' => 'menuCategory']);
            Route::resource('menu-statuses', MenuStatusController::class)->except('show');
        });

        Route::middleware('role:superadmin')->group(function () {
            Route::patch('admin-users/{admin_user}/status', [AdminUserController::class, 'updateStatus'])->name('admin-users.status');
            Route::resource('admin-users', AdminUserController::class)->except('show');
            Route::patch('customer-users/{customer}/status', [CustomerUserController::class, 'updateStatus'])->name('customer-users.status');
            Route::resource('customer-users', CustomerUserController::class)->except('show')->parameters(['customer-users' => 'customer']);
        });

        Route::middleware('can:orders.manage')->group(function () {
            Route::resource('orders', OrderController::class)->except('show');
        });

        Route::middleware('can:payments.manage')->group(function () {
            Route::resource('payments', PaymentController::class)->except('show');
        });
    });
