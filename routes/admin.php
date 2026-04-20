<?php

use App\Http\Controllers\Admin\MenuCategoryController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\TableCategoryController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\TableStatusController;
use App\Livewire\Admin\TableQrPage;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:superadmin|admin'])
    ->prefix('admin')
    ->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');

        Route::get('tables/{table}/qr', TableQrPage::class)->name('tables.qr');
        Route::patch('tables/{table}/status', [TableController::class, 'updateStatus'])->name('tables.status');

        Route::resource('menus', MenuController::class)->except('show');
        Route::resource('menu-categories', MenuCategoryController::class)
            ->except('show')
            ->parameters(['menu-categories' => 'menuCategory']);
        Route::resource('table-statuses', TableStatusController::class)
            ->except('show')
            ->parameters(['table-statuses' => 'tableStatus']);
        Route::resource('table-categories', TableCategoryController::class)
            ->except('show')
            ->parameters(['table-categories' => 'tableCategory']);
        Route::resource('tables', TableController::class)->except('show');

        Route::resource('orders', OrderController::class)->except('show');
        Route::resource('payments', PaymentController::class)->except('show');
        Route::resource('reservations', ReservationController::class)->except('show');
    });
