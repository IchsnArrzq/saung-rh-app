<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/landing.php';
require __DIR__.'/admin.php';
require __DIR__.'/customer.php';

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
