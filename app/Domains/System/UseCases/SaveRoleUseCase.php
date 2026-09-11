<?php

namespace App\Domains\System\UseCases;

use App\Domains\System\Repositories\RoleRepository;
use App\Models\Role;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

/**
 * Buat peran baru, atau ubah nama (peran buatan sendiri saja) dan hak akses
 * peran yang ada.
 *
 *  - Superadmin terkunci: aksesnya aturan Gate::before, bukan data di sini.
 *  - Nama peran bawaan dipakai kode, jadi tidak bisa diganti.
 *  - Nama unik tanpa membedakan huruf besar-kecil.
 *  - Selain superadmin, tidak ada yang bisa mengubah peran yang dipegangnya
 *    sendiri atau memberi hak akses yang tidak ia miliki — layar ini tidak boleh
 *    jadi jalan pintas menaikkan akses sendiri.
 *  - Hanya permission yang dikelola layar ini (PermissionCatalog) yang disentuh;
 *    sisanya — permission lama, restore/forceDelete — dipertahankan.
 */
class SaveRoleUseCase
{
    public function __construct(private readonly RoleRepository $roles) {}

    /**
     * @param  array<int, string>  $permissions
     */
    public function handle(?Role $role, string $name, array $permissions, User $actor): Role
    {
        if ($role?->isLocked()) {
            throw ValidationException::withMessages([
                'role' => 'Peran Superadmin terkunci: selalu memiliki semua akses dan tidak bisa diubah.',
            ]);
        }

        $actorIsSuperadmin = $actor->hasRole(Role::LOCKED);

        if ($role && ! $actorIsSuperadmin && $actor->hasRole((string) $role->name)) {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak bisa mengubah peran yang Anda pegang sendiri. Minta Superadmin untuk mengubahnya.',
            ]);
        }

        $name = trim($name);

        if (! $role?->isSystem()) {
            $this->assertNameAvailable($name, $role);
        }

        $managed = PermissionCatalog::managedNames();
        $requested = array_values(array_unique(array_intersect($permissions, $managed)));
        $current = $role ? $this->roles->permissionNames($role) : [];

        if (! $actorIsSuperadmin) {
            $held = $actor->getAllPermissions()->pluck('name')->all();
            $granting = array_diff($requested, $held, $current);

            if ($granting !== []) {
                throw ValidationException::withMessages([
                    'permissions' => 'Anda tidak bisa memberi hak akses yang tidak Anda miliki sendiri ('
                        .count($granting).' hak akses). Minta Superadmin untuk memberikannya.',
                ]);
            }
        }

        $untouched = array_values(array_diff($current, $managed));

        $role = DB::transaction(function () use ($role, $name, $requested, $untouched): Role {
            if (! $role) {
                $role = $this->roles->create($name);
            } elseif (! $role->isSystem() && $role->name !== $name) {
                $role = $this->roles->rename($role, $name);
            }

            $this->roles->syncPermissions($role, $this->roles->existingPermissionNames([...$untouched, ...$requested]));

            return $role;
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role->refresh();
    }

    private function assertNameAvailable(string $name, ?Role $role): void
    {
        if ($name === '') {
            throw ValidationException::withMessages(['name' => 'Nama peran wajib diisi.']);
        }

        $isOwnName = $role && mb_strtolower((string) $role->name) === mb_strtolower($name);

        if (! $isOwnName && in_array(mb_strtolower($name), Role::SYSTEM, true)) {
            throw ValidationException::withMessages(['name' => 'Nama ini dipakai peran bawaan. Pilih nama lain.']);
        }

        if ($this->roles->nameTaken($name, $role?->id)) {
            throw ValidationException::withMessages(['name' => 'Sudah ada peran bernama "'.$name.'".']);
        }
    }
}
