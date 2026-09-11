<?php

use App\Http\Controllers\Admin\SpecialRequestCategoryController;
use App\Models\SpecialRequestCategory;
use Illuminate\Support\Facades\Route;

/*
| Master kategori permintaan khusus — pilihan yang dilihat tamu di panel mejanya.
| Pola otorisasi sama dengan routes/admin/menus.php: `->can()` di rute, authorize()
| di komponen Livewire, @can di Blade. whereUuid: id yang bukan UUID langsung 404,
| bukan error query Postgres.
*/

Route::get('special-request-categories', [SpecialRequestCategoryController::class, 'index'])
    ->name('special-request-categories.index')
    ->can('viewAny', SpecialRequestCategory::class);

Route::get('special-request-categories/create', [SpecialRequestCategoryController::class, 'create'])
    ->name('special-request-categories.create')
    ->can('create', SpecialRequestCategory::class);

Route::get('special-request-categories/{specialRequestCategory}/edit', [SpecialRequestCategoryController::class, 'edit'])
    ->name('special-request-categories.edit')
    ->whereUuid('specialRequestCategory')
    ->can('update', 'specialRequestCategory');
