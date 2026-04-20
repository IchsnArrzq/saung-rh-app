<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class CustomerUserController extends Controller
{
    public function index()
    {
        $customers = User::role('customer')->latest()->get();
        return view('admin.customer-users.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customer-users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $customer = User::create($validated);
        $customer->assignRole('customer');

        return redirect()->route('customer-users.index')->with('success', 'Akun Customer berhasil ditambahkan.');
    }

    public function edit(User $customer)
    {
        return view('admin.customer-users.edit', compact('customer'));
    }

    public function update(Request $request, User $customer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$customer->id],
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);
            $validated['password'] = $request->password;
        }

        $customer->update($validated);

        return redirect()->route('customer-users.index')->with('success', 'Data Customer berhasil diperbarui.');
    }

    public function destroy(User $customer)
    {
        $customer->delete();
        return redirect()->route('customer-users.index')->with('success', 'Customer berhasil dihapus.');
    }

    public function updateStatus(User $customer)
    {
        $customer->update([
            'is_active' => !$customer->is_active
        ]);

        return back()->with('success', 'Status Customer berhasil diubah.');
    }
}
