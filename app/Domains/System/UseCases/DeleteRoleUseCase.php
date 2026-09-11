<?php

namespace App\Domains\System\UseCases;

use App\Domains\System\Repositories\RoleRepository;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

/**
 * Hapus peran buatan sendiri yang sudah tidak dipakai siapa pun. Peran bawaan
 * dipakai kode dan tidak pernah dihapus; peran yang masih dipegang karyawan
 * harus dikosongkan dulu supaya tidak ada akun yang tiba-tiba kehilangan akses.
 */
class DeleteRoleUseCase
{
    public function __construct(private readonly RoleRepository $roles) {}

    public function handle(Role $role, User $actor): void
    {
        if ($role->isSystem()) {
            throw ValidationException::withMessages([
                'role' => 'Peran bawaan dipakai sistem dan tidak bisa dihapus.',
            ]);
        }

        if (! $actor->hasRole(Role::LOCKED) && $actor->hasRole((string) $role->name)) {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak bisa menghapus peran yang Anda pegang sendiri.',
            ]);
        }

        $holders = $this->roles->userCount($role);

        if ($holders > 0) {
            throw ValidationException::withMessages([
                'role' => 'Peran "'.$role->label().'" masih dipakai '.$holders.' akun. Pindahkan mereka ke peran lain dulu.',
            ]);
        }

        DB::transaction(fn () => $this->roles->delete($role));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
