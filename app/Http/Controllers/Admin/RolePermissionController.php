<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Support\PolicyPermissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();

        $permissionGroups = PolicyPermissions::groups();

        return view('admin.settings.roles-permissions', compact('roles', 'permissionGroups'));
    }

    public function update(Request $request, Role $role)
    {
        abort_if($role->name === 'superadmin', 403, 'Permission Superadmin tidak dapat diubah.');

        $managedPermissions = PolicyPermissions::names();

        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in($managedPermissions)],
        ]);

        // This screen only manages the policy-based permission matrix. Any
        // other permission the role already has (e.g. legacy feature-level
        // permissions) falls outside that matrix and must be preserved.
        $untouchedPermissions = $role->permissions->pluck('name')
            ->reject(fn (string $name) => in_array($name, $managedPermissions, true))
            ->all();

        $role->syncPermissions([...$untouchedPermissions, ...($validated['permissions'] ?? [])]);

        return back()->with('success', "Permission untuk role \"{$role->name}\" berhasil diperbarui.");
    }
}
