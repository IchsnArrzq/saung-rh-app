<?php

use App\Http\Controllers\Admin\ReservationController;
use App\Models\Reservation;
use Illuminate\Support\Facades\Route;

/*
| Pola otorisasi: lihat routes/admin/menus.php dan AGENTS.md § Authorization.
|
| `Route::resource` dilepas — ia mendaftarkan store/update/destroy ke metode
| yang tidak ada di ReservationController (tulisan dikerjakan Livewire).
*/

Route::get('reservations', [ReservationController::class, 'index'])
    ->name('reservations.index')
    ->can('viewAny', Reservation::class);

Route::get('reservations/create', [ReservationController::class, 'create'])
    ->name('reservations.create')
    ->can('create', Reservation::class);

Route::get('reservations/{reservation}/edit', [ReservationController::class, 'edit'])
    ->name('reservations.edit')
    ->can('update', 'reservation');
