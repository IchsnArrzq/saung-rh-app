<?php

use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['demo.login', 'auth', 'verified'])->group(function () {
    Route::middleware('role:superadmin|admin|manager')
        ->prefix('manager')
        ->group(function () {
            Route::get('dashboard', [PortalController::class, 'manager'])->name('manager.dashboard');
        });

    Route::middleware('role:superadmin|admin|receptionist|manager')
        ->prefix('receptionist')
        ->group(function () {
            Route::get('dashboard', [PortalController::class, 'receptionist'])->name('receptionist.dashboard');

            Route::middleware('can:receptionist.monitor')->group(function () {
                Route::get('table-map', [PortalController::class, 'receptionistTableMap'])->name('receptionist.table-map');
                Route::get('visitors', [PortalController::class, 'receptionistVisitors'])->name('receptionist.visitors');
                Route::get('analytics', [PortalController::class, 'receptionistAnalytics'])->name('receptionist.analytics');
            });

            Route::middleware('can:reservations.manage')
                ->get('bookings', [PortalController::class, 'receptionistBookings'])->name('receptionist.bookings');
        });

    Route::middleware('role:superadmin|admin|waiter')
        ->prefix('waiter')
        ->group(function () {
            Route::get('dashboard', [PortalController::class, 'waiter'])->name('waiter.dashboard');

            Route::middleware('can:tables.status.update')
                ->get('tables', [PortalController::class, 'waiterTables'])->name('waiter.tables');

            Route::middleware('can:waiter.operate')
                ->get('tips', [PortalController::class, 'waiterTips'])->name('waiter.tips');
        });

    Route::middleware('role:superadmin|admin|ob')
        ->prefix('ob')
        ->group(function () {
            Route::get('dashboard', [PortalController::class, 'ob'])->name('ob.dashboard');

            Route::middleware('can:tables.status.update')
                ->get('tables', [PortalController::class, 'obTables'])->name('ob.tables');
        });
});
