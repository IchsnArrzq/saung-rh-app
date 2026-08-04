<?php

use App\Livewire\Customer\MenuOrder;
use App\Livewire\Customer\TablePicker;
use Illuminate\Support\Facades\Route;

// Dine-in ordering (Livewire full-page): pick a table, then order.
Route::get('menu/tables', TablePicker::class)->name('menus.tables');
Route::get('menu', MenuOrder::class)->name('menus.index');
