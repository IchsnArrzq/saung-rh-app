<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\CustomerUserController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
| Pola otorisasi: lihat routes/admin/menus.php dan AGENTS.md § Authorization.
|
| Kedua layar mengelola baris `users` yang sama — yang membedakan hanya role
| yang ditampilkan — jadi keduanya dijaga UserPolicy. Tanpa gerbang ini kasir
| ikut lolos dari `role:…|cashier` di routes/admin.php dan bisa membuka, membuat,
| serta menghapus akun.
|
| `middlewareFor` dipakai supaya tiap aksi resource memanggil ability-nya
| sendiri (index → viewAny, store → create, destroy → delete), bukan satu
| ability untuk semuanya. Argumen kedua yang bukan nama kelas — 'admin_user',
| 'customer' — adalah NAMA PARAMETER route: Laravel meneruskan model hasil
| binding ke policy.
*/

Route::patch('admin-users/{admin_user}/status', [AdminUserController::class, 'updateStatus'])
    ->name('admin-users.status')
    ->can('update', 'admin_user');

Route::resource('admin-users', AdminUserController::class)
    ->except('show')
    ->middlewareFor('index', 'can:viewAny,'.User::class)
    ->middlewareFor(['create', 'store'], 'can:create,'.User::class)
    ->middlewareFor(['edit', 'update'], 'can:update,admin_user')
    ->middlewareFor('destroy', 'can:delete,admin_user');

Route::patch('customer-users/{customer}/status', [CustomerUserController::class, 'updateStatus'])
    ->name('customer-users.status')
    ->can('update', 'customer');

Route::resource('customer-users', CustomerUserController::class)
    ->except('show')
    ->parameters(['customer-users' => 'customer'])
    ->middlewareFor('index', 'can:viewAny,'.User::class)
    ->middlewareFor(['create', 'store'], 'can:create,'.User::class)
    ->middlewareFor(['edit', 'update'], 'can:update,customer')
    ->middlewareFor('destroy', 'can:delete,customer');

// Roles & Permissions management (superadmin only)
Route::middleware('role:superadmin')->prefix('settings')->name('settings.')->group(function () {
    Route::get('roles-permissions', [RolePermissionController::class, 'index'])->name('roles-permissions');
    Route::patch('roles-permissions/{role}', [RolePermissionController::class, 'update'])->name('roles-permissions.update');
});
