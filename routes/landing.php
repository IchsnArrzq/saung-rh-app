<?php

use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicMenuCartController;
use App\Http\Controllers\PublicMenuController;
use App\Livewire\Frontend\CartCheckout;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicHomeController::class)->name('public.home');
Route::get('menu', PublicMenuController::class)->name('public.menu');
Route::post('menu/{menu}/cart', [PublicMenuCartController::class, 'store'])->name('public.menu.cart.store');
Route::get('cart', CartCheckout::class)->name('public.cart.index');
