<?php

use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\MenuCategoryController;
use App\Http\Controllers\Admin\MenuController;
use Illuminate\Support\Facades\Route;

Route::resource('menus', MenuController::class)->except('show');
Route::resource('menu-categories', MenuCategoryController::class)
    ->except('show')
    ->parameters(['menu-categories' => 'menuCategory']);

Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
Route::get('menus/create', [MenuController::class, 'create'])->name('menus.create');
Route::get('menus/{menu}/edit', [MenuController::class, 'edit'])->name('menus.edit');
Route::get('menus/{menu}/media', [MenuController::class, 'media'])->name('menus.media.edit');

Route::get('menu-categories', [MenuCategoryController::class, 'index'])->name('menu-categories.index');
Route::get('menu-categories/create', [MenuCategoryController::class, 'create'])->name('menu-categories.create');
Route::get('menu-categories/{menuCategory}/edit', [MenuCategoryController::class, 'edit'])->name('menu-categories.edit');


// Resep per menu
Route::get('menus/{menu}/ingredients', [IngredientController::class, 'menuIngredients'])->name('menus.ingredients.edit');
