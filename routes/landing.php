<?php

use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicMenuCartController;
use App\Livewire\Frontend\CartCheckout;
use App\Livewire\Frontend\MenuCatalog;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicHomeController::class)->name('public.home');
Route::get('menu', MenuCatalog::class)->name('public.menu.index');
Route::post('menu/{menu}/cart', [PublicMenuCartController::class, 'store'])->name('public.menu.cart.store');
Route::get('cart', CartCheckout::class)->name('public.cart.index');
