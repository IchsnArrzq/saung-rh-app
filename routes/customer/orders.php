<?php

use App\Livewire\Customer\OrderHistory;
use Illuminate\Support\Facades\Route;

// Pesanan milik akun yang sedang login, terbaru lebih dulu.
Route::get('orders', OrderHistory::class)->name('orders.index');
