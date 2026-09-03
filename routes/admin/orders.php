<?php

use App\Http\Controllers\Admin\OrderController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

/*
| Pola otorisasi: lihat routes/admin/menus.php dan AGENTS.md § Authorization.
|
| `Route::resource` dilepas — ia mendaftarkan store/update/destroy ke metode
| yang tidak ada di OrderController (tulisan dikerjakan komponen Livewire),
| jadi tidak ada apa pun di sana yang bisa dijaga.
*/

Route::get('orders', [OrderController::class, 'index'])
    ->name('orders.index')
    ->can('viewAny', Order::class);

Route::get('orders/create', [OrderController::class, 'create'])
    ->name('orders.create')
    ->can('create', Order::class);

Route::get('orders/{order}/edit', [OrderController::class, 'edit'])
    ->name('orders.edit')
    ->can('update', 'order');
