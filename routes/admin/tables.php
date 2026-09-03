<?php

use App\Http\Controllers\Admin\TableCategoryController;
use App\Http\Controllers\Admin\TableController;
use App\Livewire\Admin\TableQrPage;
use App\Models\Table;
use App\Models\TableCategory;
use Illuminate\Support\Facades\Route;

/*
| Pola otorisasi: lihat routes/admin/menus.php dan AGENTS.md § Authorization.
|
| Dua route dibuang di sini, keduanya menunjuk metode yang tidak ada di
| controller-nya:
|   - `Route::resource` untuk tables & table-categories (store/update/destroy —
|     tulisan dikerjakan komponen Livewire);
|   - `PATCH tables/{table}/status` ke TableController::updateStatus, yang tidak
|     pernah ditulis dan tidak pernah dipanggil. Perubahan status meja lewat
|     Admin\Tables\Table::updateStatus dan StatusBoard::moveTable.
*/

// Halaman QR hanya menampilkan meja, jadi cukup ability baca.
Route::get('tables/{table}/qr', TableQrPage::class)
    ->name('tables.qr')
    ->can('view', 'table');

Route::get('tables', [TableController::class, 'index'])
    ->name('tables.index')
    ->can('viewAny', Table::class);

Route::get('tables/create', [TableController::class, 'create'])
    ->name('tables.create')
    ->can('create', Table::class);

Route::get('tables/{table}/edit', [TableController::class, 'edit'])
    ->name('tables.edit')
    ->can('update', 'table');

Route::get('table-categories', [TableCategoryController::class, 'index'])
    ->name('table-categories.index')
    ->can('viewAny', TableCategory::class);

Route::get('table-categories/create', [TableCategoryController::class, 'create'])
    ->name('table-categories.create')
    ->can('create', TableCategory::class);

Route::get('table-categories/{tableCategory}/edit', [TableCategoryController::class, 'edit'])
    ->name('table-categories.edit')
    ->can('update', 'tableCategory');
