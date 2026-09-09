<?php

use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\StockOpnameController;
use App\Http\Controllers\Admin\SupplierController;
use App\Models\Ingredient;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\Supplier;
use Illuminate\Support\Facades\Route;

/*
| Pola otorisasi: lihat routes/admin/menus.php dan AGENTS.md § Authorization.
|
| Grup induk di routes/admin.php meloloskan kasir juga (`role:…|cashier`), jadi
| tanpa `->can()` seluruh modul inventori terbuka untuk kasir. Gerbang policy di
| bawah ini yang membatasinya ke role yang memang dapat izinnya.
*/

// Ingredients (Bahan Makanan)
Route::get('ingredients', [IngredientController::class, 'index'])
    ->name('ingredients.index')
    ->can('viewAny', Ingredient::class);

Route::get('ingredients/create', [IngredientController::class, 'create'])
    ->name('ingredients.create')
    ->can('create', Ingredient::class);

Route::get('ingredients/{ingredient}/edit', [IngredientController::class, 'edit'])
    ->name('ingredients.edit')
    ->can('update', 'ingredient');

// Stock Opname (header + detail physical count)
Route::get('stock-opnames', [StockOpnameController::class, 'index'])
    ->name('stock-opnames.index')
    ->can('viewAny', StockOpname::class);

Route::get('stock-opnames/create', [StockOpnameController::class, 'create'])
    ->name('stock-opnames.create')
    ->can('create', StockOpname::class);

Route::get('stock-opnames/{stockOpname}/edit', [StockOpnameController::class, 'edit'])
    ->name('stock-opnames.edit')
    ->can('update', 'stockOpname');

// Riwayat Stok (stock movement ledger, read-only)
Route::get('stock-movements', [StockOpnameController::class, 'movements'])
    ->name('stock-movements.index')
    ->can('viewAny', StockMovement::class);

// Contacts: Supplier
Route::get('suppliers', [SupplierController::class, 'index'])
    ->name('suppliers.index')
    ->can('viewAny', Supplier::class);

Route::get('suppliers/create', [SupplierController::class, 'create'])
    ->name('suppliers.create')
    ->can('create', Supplier::class);

Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])
    ->name('suppliers.edit')
    ->can('update', 'supplier');

// Pembelian (Purchases)
Route::get('purchases', [PurchaseController::class, 'index'])
    ->name('purchases.index')
    ->can('viewAny', Purchase::class);

Route::get('purchases/create', [PurchaseController::class, 'create'])
    ->name('purchases.create')
    ->can('create', Purchase::class);

Route::get('purchases/{purchase}/edit', [PurchaseController::class, 'edit'])
    ->name('purchases.edit')
    ->can('update', 'purchase');

// Penjualan (Sales)
Route::get('sales', [SaleController::class, 'index'])
    ->name('sales.index')
    ->can('viewAny', Sale::class);

Route::get('sales/create', [SaleController::class, 'create'])
    ->name('sales.create')
    ->can('create', Sale::class);

Route::get('sales/{sale}/edit', [SaleController::class, 'edit'])
    ->name('sales.edit')
    ->can('update', 'sale');

// Stok (overview) — halamannya menampilkan stok bahan, jadi gerbangnya sama
// dengan daftar bahan, bukan ability tersendiri.
Route::get('stock', [StockController::class, 'index'])
    ->name('stock.index')
    ->can('viewAny', Ingredient::class);
