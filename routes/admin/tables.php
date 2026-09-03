<?php

use App\Http\Controllers\Admin\TableCategoryController;
use App\Http\Controllers\Admin\TableController;
use App\Livewire\Admin\TableQrPage;
use Illuminate\Support\Facades\Route;

Route::get('tables/{table}/qr', TableQrPage::class)->name('tables.qr');
Route::patch('tables/{table}/status', [TableController::class, 'updateStatus'])->name('tables.status');

Route::resource('table-categories', TableCategoryController::class)
    ->except('show')
    ->parameters(['table-categories' => 'tableCategory']);
Route::resource('tables', TableController::class)->except('show');

Route::get('table-categories', [TableCategoryController::class, 'index'])->name('table-categories.index');
Route::get('table-categories/create', [TableCategoryController::class, 'create'])->name('table-categories.create');
Route::get('table-categories/{tableCategory}/edit', [TableCategoryController::class, 'edit'])->name('table-categories.edit');

Route::get('tables', [TableController::class, 'index'])->name('tables.index');
Route::get('tables/create', [TableController::class, 'create'])->name('tables.create');
Route::get('tables/{table}/edit', [TableController::class, 'edit'])->name('tables.edit');
