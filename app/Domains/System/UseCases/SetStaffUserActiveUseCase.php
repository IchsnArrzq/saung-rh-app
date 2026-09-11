<?php

namespace App\Domains\System\UseCases;

use App\Domains\System\Repositories\UserRepository;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Aktifkan atau nonaktifkan akun karyawan dari daftar. Akun nonaktif tidak bisa
 * masuk (LoginForm), tapi riwayatnya tetap utuh — cara aman "mengeluarkan"
 * karyawan yang datanya masih dirujuk pesanan atau shift.
 */
class SetStaffUserActiveUseCase
{
    public function __construct(private readonly UserRepository $users) {}

    public function handle(User $user, bool $active, User $actor): User
    {
        if ($user->hasRole(Role::LOCKED)) {
            throw ValidationException::withMessages(['user' => 'Akun Superadmin selalu aktif.']);
        }

        if (! $active && $user->is($actor)) {
            throw ValidationException::withMessages(['user' => 'Anda tidak bisa menonaktifkan akun Anda sendiri.']);
        }

        return DB::transaction(fn (): User => $this->users->update($user, ['is_active' => $active]));
    }
}
