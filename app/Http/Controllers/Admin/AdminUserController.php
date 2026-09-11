<?php

namespace App\Http\Controllers\Admin;

use App\Domains\System\DTO\StaffUserData;
use App\Domains\System\QueryUseCases\GetStaffUsersQueryUseCase;
use App\Domains\System\UseCases\DeleteStaffUserUseCase;
use App\Domains\System\UseCases\SaveStaffUserUseCase;
use App\Domains\System\UseCases\SetStaffUserActiveUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\StaffUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Layar Karyawan — semua akun staf restoran apa pun perannya, termasuk peran
 * buatan layar Peran & hak akses (dulu hanya admin & kasir). Rute + UserPolicy
 * menjaga siapa yang boleh membukanya; aturan Superadmin dan akun sendiri ada di
 * UseCase. ValidationException dari UseCase otomatis kembali ke form beserta
 * pesannya.
 */
class AdminUserController extends Controller
{
    public function index(Request $request, GetStaffUsersQueryUseCase $staff): View
    {
        $search = trim((string) $request->query('q', ''));
        $role = (string) $request->query('peran', '');

        return view('admin.admin-users.index', [
            'users' => $staff->paginate($search, $role),
            'roleOptions' => $staff->filterRoleOptions(),
            'search' => $search,
            'role' => $role,
        ]);
    }

    public function create(GetStaffUsersQueryUseCase $staff): View
    {
        return view('admin.admin-users.create', [
            'roleOptions' => $staff->assignableRoleOptions(),
        ]);
    }

    public function store(StaffUserRequest $request, SaveStaffUserUseCase $saveUser): RedirectResponse
    {
        $user = $saveUser->handle($this->data($request), $request->user());

        return redirect()->route('admin-users.index')->with('success', 'Akun '.$user->name.' ditambahkan.');
    }

    public function edit(User $admin_user, GetStaffUsersQueryUseCase $staff): View
    {
        return view('admin.admin-users.edit', [
            'admin_user' => $admin_user->loadMissing('roles'),
            'roleOptions' => $staff->assignableRoleOptions(),
        ]);
    }

    public function update(StaffUserRequest $request, User $admin_user, SaveStaffUserUseCase $saveUser): RedirectResponse
    {
        $user = $saveUser->handle($this->data($request), $request->user(), $admin_user);

        return redirect()->route('admin-users.index')->with('success', 'Akun '.$user->name.' diperbarui.');
    }

    public function destroy(Request $request, User $admin_user, DeleteStaffUserUseCase $deleteUser): RedirectResponse
    {
        $name = $admin_user->name;

        $deleteUser->handle($admin_user, $request->user());

        return redirect()->route('admin-users.index')->with('success', 'Akun '.$name.' dihapus.');
    }

    public function updateStatus(Request $request, User $admin_user, SetStaffUserActiveUseCase $setActive): RedirectResponse
    {
        $user = $setActive->handle($admin_user, ! $admin_user->is_active, $request->user());

        return back()->with('success', $user->is_active
            ? 'Akun '.$user->name.' diaktifkan lagi.'
            : 'Akun '.$user->name.' dinonaktifkan — ia tidak bisa masuk sampai diaktifkan lagi.');
    }

    private function data(StaffUserRequest $request): StaffUserData
    {
        $validated = $request->validated();

        return new StaffUserData(
            name: trim((string) $validated['name']),
            email: (string) $validated['email'],
            phone: filled($validated['phone'] ?? null) ? trim((string) $validated['phone']) : null,
            password: filled($validated['password'] ?? null) ? (string) $validated['password'] : null,
            role: filled($validated['role'] ?? null) ? (string) $validated['role'] : null,
            isActive: (bool) ($validated['is_active'] ?? false),
        );
    }
}
