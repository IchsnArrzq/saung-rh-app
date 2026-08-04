<?php

use App\Http\Controllers\Admin\ReservationController;
use Illuminate\Support\Facades\Route;

Route::resource('reservations', ReservationController::class)->except('show');

Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
Route::get('reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
Route::get('reservations/{reservation}/edit', [ReservationController::class, 'edit'])->name('reservations.edit');
