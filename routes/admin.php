<?php

use Illuminate\Support\Facades\Route;

// Gerbang kasar area back-office. Role bawaan lolos lewat namanya persis seperti
// dulu; role baru dari layar Peran & hak akses lolos lewat `backoffice.access`.
// Yang benar-benar menentukan tiap halaman tetap `->can()` di modulnya.
Route::middleware(['demo.login', 'auth', 'verified', 'role_or_permission:superadmin|admin|cashier|backoffice.access'])
    ->prefix('admin')
    ->group(function () {
        require __DIR__.'/admin/dashboard.php';
        require __DIR__.'/admin/tables.php';
        require __DIR__.'/admin/menus.php';
        require __DIR__.'/admin/orders.php';
        require __DIR__.'/admin/payments.php';
        require __DIR__.'/admin/reservations.php';
        require __DIR__.'/admin/reports.php';
        require __DIR__.'/admin/users.php';
        require __DIR__.'/admin/system.php';
        require __DIR__.'/admin/inventory.php';
        require __DIR__.'/admin/customers.php';
        require __DIR__.'/admin/special-requests.php';
    });
