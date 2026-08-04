<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\CustomerUserController;
use App\Http\Controllers\Admin\RolePermissionController;
use Illuminate\Support\Facades\Route;

Route::patch('admin-users/{admin_user}/status', [AdminUserController::class, 'updateStatus'])->name('admin-users.status');
Route::resource('admin-users', AdminUserController::class)->except('show');

Route::patch('customer-users/{customer}/status', [CustomerUserController::class, 'updateStatus'])->name('customer-users.status');
Route::resource('customer-users', CustomerUserController::class)
    ->except('show')
    ->parameters(['customer-users' => 'customer']);

// Roles & Permissions management (superadmin only)
Route::middleware('role:superadmin')->prefix('settings')->name('settings.')->group(function () {
    Route::get('roles-permissions', [RolePermissionController::class, 'index'])->name('roles-permissions');
    Route::patch('roles-permissions/{role}', [RolePermissionController::class, 'update'])->name('roles-permissions.update');
});
