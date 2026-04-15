<?php

use App\Http\Controllers\IngredientController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.home')->name('public.home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::resource('menus', MenuController::class)->except('show');
    Route::resource('menu-categories', MenuCategoryController::class)
        ->except('show')
        ->parameters(['menu-categories' => 'menuCategory']);
    Route::resource('tables', TableController::class)->except('show');

    Route::resource('orders', OrderController::class)->except('show');
    Route::resource('payments', PaymentController::class)->except('show');

    Route::resource('promotions', PromotionController::class)->except('show');
    Route::resource('reservations', ReservationController::class)->except('show');
    Route::resource('ingredients', IngredientController::class)->except('show');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
