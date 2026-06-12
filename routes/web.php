<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/landing.php';
require __DIR__.'/admin.php';
require __DIR__.'/customer.php';
require __DIR__.'/pos.php';
require __DIR__.'/kds.php';

Route::view('profile', 'profile')
    ->middleware(['demo.login', 'auth'])
    ->name('profile');

require __DIR__.'/auth.php';
