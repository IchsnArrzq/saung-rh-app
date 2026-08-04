<?php

use App\Http\Controllers\Admin\SystemController;
use Illuminate\Support\Facades\Route;

// System administration (superadmin/admin only)
Route::middleware('role:superadmin|admin')->prefix('system')->name('system.')->group(function () {
    Route::get('settings', [SystemController::class, 'settings'])->name('settings');
    Route::get('payment-accounts', [SystemController::class, 'paymentAccounts'])->name('payment-accounts');
    Route::get('license', [SystemController::class, 'license'])->name('license');
});
