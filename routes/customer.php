<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['demo.login', 'auth', 'verified', 'role:customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {
        require __DIR__.'/customer/home.php';
        require __DIR__.'/customer/menu.php';
        require __DIR__.'/customer/reservations.php';
    });
