<?php

use App\Http\Controllers\CheckInController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicMenuCartController;
use App\Http\Controllers\PublicMenuController;
use App\Http\Controllers\PwaManifestController;
use App\Livewire\Frontend\CartCheckout;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicHomeController::class)->name('public.home');
Route::get('t/{token}', CheckInController::class)->name('checkin.show');
Route::get('menu', PublicMenuController::class)->name('public.menu');
Route::get('menu/{menu}', [PublicMenuController::class, 'show'])->name('public.menu.show');
Route::post('menu/{menu}/cart', [PublicMenuCartController::class, 'store'])->name('public.menu.cart.store');
Route::get('cart', CartCheckout::class)->name('public.cart.index');

// Served by Laravel rather than from public/ so the install prompt shows the
// name the admin set on Pengaturan Aplikasi. The URL matches what sw.js
// precaches -- don't rename it without updating public/sw.js too.
Route::get('manifest.webmanifest', PwaManifestController::class)->name('pwa.manifest');
