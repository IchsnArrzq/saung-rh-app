<?php

use App\Livewire\Customer\Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', Dashboard::class)->name('dashboard');
