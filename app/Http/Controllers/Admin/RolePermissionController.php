<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\View\View;

/**
 * Pembungkus halaman Peran & hak akses. Daftar, form, dan penyimpanannya hidup
 * di App\Livewire\Admin\Roles\{Table,Form}.
 */
class RolePermissionController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.roles.index');
    }

    public function create(): View
    {
        return view('admin.settings.roles.create');
    }

    public function edit(Role $role): View
    {
        return view('admin.settings.roles.edit', ['role' => $role]);
    }
}
