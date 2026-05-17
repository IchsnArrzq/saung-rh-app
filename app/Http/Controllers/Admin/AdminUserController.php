<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::role(['admin', 'superadmin'])->latest()->get();
        return view('admin.admin-users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.admin-users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $user = User::create($validated);
        $user->assignRole('admin');

        return redirect()->route('admin-users.index')->with('success', 'Akun Admin berhasil ditambahkan.');
    }

    public function edit(User $admin_user)
    {
        return view('admin.admin-users.edit', compact('admin_user'));
    }

    public function update(Request $request, User $admin_user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$admin_user->id],
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);
            $validated['password'] = $request->password;
        }

        $admin_user->update($validated);

        return redirect()->route('admin-users.index')->with('success', 'Data Admin berhasil diperbarui.');
    }

    public function destroy(User $admin_user)
    {
        if ($admin_user->hasRole('superadmin')) {
            return back()->with('error', 'Superadmin tidak dapat dihapus.');
        }
        
        $admin_user->delete();
        return redirect()->route('admin-users.index')->with('success', 'Admin berhasil dihapus.');
    }

    public function updateStatus(User $admin_user)
    {
        if ($admin_user->hasRole('superadmin')) {
            return back()->with('error', 'Status Superadmin tidak dapat dinonaktifkan.');
        }

        $admin_user->update([
            'is_active' => !$admin_user->is_active
        ]);

        return back()->with('success', 'Status Admin berhasil diubah.');
    }
}
