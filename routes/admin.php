<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['demo.login', 'auth', 'verified', 'role:superadmin|admin|cashier'])
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
    });
