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

    Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
    Route::get('menus/create', [MenuController::class, 'create'])->name('menus.create');
    Route::get('menus/{menu}/edit', [MenuController::class, 'edit'])->name('menus.edit');

    Route::get('menu-categories', [MenuCategoryController::class, 'index'])->name('menu-categories.index');
    Route::get('menu-categories/create', [MenuCategoryController::class, 'create'])->name('menu-categories.create');
    Route::get('menu-categories/{menuCategory}/edit', [MenuCategoryController::class, 'edit'])->name('menu-categories.edit');

    Route::get('table-statuses', [TableStatusController::class, 'index'])->name('table-statuses.index');
    Route::get('table-statuses/create', [TableStatusController::class, 'create'])->name('table-statuses.create');
    Route::get('table-statuses/{tableStatus}/edit', [TableStatusController::class, 'edit'])->name('table-statuses.edit');

    Route::get('table-categories', [TableCategoryController::class, 'index'])->name('table-categories.index');
    Route::get('table-categories/create', [TableCategoryController::class, 'create'])->name('table-categories.create');
    Route::get('table-categories/{tableCategory}/edit', [TableCategoryController::class, 'edit'])->name('table-categories.edit');

    Route::get('tables', [TableController::class, 'index'])->name('tables.index');
    Route::get('tables/create', [TableController::class, 'create'])->name('tables.create');
    Route::get('tables/{table}/edit', [TableController::class, 'edit'])->name('tables.edit');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::get('payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');

    Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::get('reservations/{reservation}/edit', [ReservationController::class, 'edit'])->name('reservations.edit');
});
