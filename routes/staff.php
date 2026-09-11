<?php

use App\Http\Controllers\PortalController;
use App\Models\SongRequest;
use App\Models\SpecialRequest;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal staf lantai
|--------------------------------------------------------------------------
|
| Setiap rute dijaga permission (`can:`), bukan daftar role. Gerbang lama
| `role:superadmin|admin|waiter` mengunci role baru dari layar Peran & hak
| akses di luar, seberapa lengkap pun permission-nya (AGENTS.md § Role gates
| in routes). Superadmin lolos lewat Gate::before.
|
| Permission fitur (`waiter.operate`, `receptionist.monitor`, …) dibuat oleh
| PermissionSeeder; permission model (`special_request.viewAny`) oleh
| PolicyPermissionSeeder.
*/

Route::middleware(['demo.login', 'auth', 'verified'])->group(function () {
    // Kartu meja realtime + permintaan khusus, lagu, dan obrolan per meja.
    // Tidak ada lagi meja persetujuan manajer: pelayan, resepsionis, dan kasir
    // menangani permintaan langsung dari sini.
    Route::get('panel-meja', [PortalController::class, 'floor'])
        ->name('floor.index')
        ->can('viewAny', SpecialRequest::class);

    Route::get('song-queue', [PortalController::class, 'songQueue'])
        ->name('songs.queue')
        ->can('viewAny', SongRequest::class);

    Route::prefix('manager')
        ->middleware('can:manager.dashboard')
        ->group(function () {
            Route::get('dashboard', [PortalController::class, 'manager'])->name('manager.dashboard');
            Route::get('shifts', [PortalController::class, 'managerShifts'])->name('manager.shifts');
            Route::get('kpi', [PortalController::class, 'managerKpi'])->name('manager.kpi');
            Route::get('top-customers', [PortalController::class, 'managerTopCustomers'])->name('manager.top-customers');
        });

    Route::prefix('receptionist')->group(function () {
        Route::middleware('can:receptionist.monitor')->group(function () {
            Route::get('dashboard', [PortalController::class, 'receptionist'])->name('receptionist.dashboard');
            Route::get('table-map', [PortalController::class, 'receptionistTableMap'])->name('receptionist.table-map');
            Route::get('visitors', [PortalController::class, 'receptionistVisitors'])->name('receptionist.visitors');
            Route::get('analytics', [PortalController::class, 'receptionistAnalytics'])->name('receptionist.analytics');
        });

        Route::get('bookings', [PortalController::class, 'receptionistBookings'])
            ->name('receptionist.bookings')
            ->middleware('can:reservations.manage');
    });

    Route::prefix('waiter')->group(function () {
        Route::get('dashboard', [PortalController::class, 'waiter'])
            ->name('waiter.dashboard')
            ->middleware('can:waiter.operate');

        Route::get('tables', [PortalController::class, 'waiterTables'])
            ->name('waiter.tables')
            ->middleware('can:tables.status.update');

        Route::get('tips', [PortalController::class, 'waiterTips'])
            ->name('waiter.tips')
            ->middleware('can:waiter.operate');
    });

    Route::prefix('ob')
        ->middleware('can:tables.status.update')
        ->group(function () {
            Route::get('dashboard', [PortalController::class, 'ob'])->name('ob.dashboard');
            Route::get('tables', [PortalController::class, 'obTables'])->name('ob.tables');
        });
});
