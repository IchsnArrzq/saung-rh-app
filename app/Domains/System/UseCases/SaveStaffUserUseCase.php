<?php

namespace App\Domains\System\UseCases;

use App\Domains\System\DTO\StaffUserData;
use App\Domains\System\Repositories\RoleRepository;
use App\Domains\System\Repositories\UserRepository;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Tambah atau ubah akun karyawan.
 *
 * Superadmin tidak bisa diubah-ubah dari sini: peran "superadmin" tidak pernah
 * bisa dipilih, akun Superadmin hanya bisa disunting oleh Superadmin sendiri —
 * itu pun hanya nama, email, nomor HP, dan kata sandinya; perannya tetap dan
 * akunnya selalu aktif. Siapa pun juga tidak bisa mengganti peran atau
 * menonaktifkan akunnya sendiri, supaya tidak mengunci dirinya keluar.
 */
class SaveStaffUserUseCase
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RoleRepository $roles,
    ) {}

    public function handle(StaffUserData $data, User $actor, ?User $user = null): User
    {
        $isSuperadminAccount = $user?->hasRole(Role::LOCKED) ?? false;
        $isSelf = $user !== null && $user->is($actor);

        if ($isSuperadminAccount && ! $actor->hasRole(Role::LOCKED)) {
            throw ValidationException::withMessages([
                'user' => 'Akun Superadmin hanya bisa diubah oleh Superadmin sendiri.',
            ]);
        }

        $roleName = $isSuperadminAccount ? null : $this->assignableRole($data->role, $isSelf ? $user : null);

        if (! $data->isActive && ($isSelf || $isSuperadminAccount)) {
            throw ValidationException::withMessages([
                'is_active' => $isSelf
                    ? 'Anda tidak bisa menonaktifkan akun Anda sendiri.'
                    : 'Akun Superadmin tidak bisa dinonaktifkan.',
            ]);
        }

        $attributes = [
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'is_active' => $data->isActive,
        ];

        if ($data->password !== null) {
            // Di-hash oleh cast `hashed` pada User.
            $attributes['password'] = $data->password;
        }

        return DB::transaction(function () use ($user, $attributes, $roleName): User {
            $user = $user ? $this->users->update($user, $attributes) : $this->users->create($attributes);

            if ($roleName !== null) {
                $this->users->syncRole($user, $roleName);
            }

            return $user;
        });
    }

    /**
     * @param  User|null  $self  akun milik pengubah sendiri (peran tidak boleh berganti)
     */
    private function assignableRole(?string $name, ?User $self): string
    {
        $role = $name !== null ? $this->roles->findByName($name) : null;

        if (! $role || $role->isLocked() || $role->name === 'customer') {
            throw ValidationException::withMessages([
                'role' => 'Pilih salah satu peran karyawan yang tersedia.',
            ]);
        }

        if ($self && ! $self->hasRole((string) $role->name)) {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak bisa mengganti peran akun Anda sendiri.',
            ]);
        }

        return (string) $role->name;
    }
}
