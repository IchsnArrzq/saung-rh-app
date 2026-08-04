<?php

use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\StockOpnameController;
use App\Http\Controllers\Admin\SupplierController;
use Illuminate\Support\Facades\Route;

// Ingredients (Bahan Makanan)
Route::get('ingredients', [IngredientController::class, 'index'])->name('ingredients.index');
Route::get('ingredients/create', [IngredientController::class, 'create'])->name('ingredients.create');
Route::get('ingredients/{ingredient}/edit', [IngredientController::class, 'edit'])->name('ingredients.edit');

// Stock Opname (header + detail physical count)
Route::get('stock-opnames', [StockOpnameController::class, 'index'])->name('stock-opnames.index');
Route::get('stock-opnames/create', [StockOpnameController::class, 'create'])->name('stock-opnames.create');
Route::get('stock-opnames/{stockOpname}/edit', [StockOpnameController::class, 'edit'])->name('stock-opnames.edit');

// Riwayat Stok (stock movement ledger, read-only)
Route::get('stock-movements', [StockOpnameController::class, 'movements'])->name('stock-movements.index');

// Contacts: Supplier
Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');

// Pembelian (Purchases)
Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
Route::get('purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');

// Penjualan (Sales)
Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create');
Route::get('sales/{sale}/edit', [SaleController::class, 'edit'])->name('sales.edit');

// Stok (overview)
Route::get('stock', [StockController::class, 'index'])->name('stock.index');
