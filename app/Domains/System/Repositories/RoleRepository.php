<?php

namespace App\Domains\System\Repositories;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class RoleRepository
{
    public function find(string $id): ?Role
    {
        return Str::isUuid($id) ? Role::query()->find($id) : null;
    }

    public function findByName(string $name): ?Role
    {
        return Role::query()->where('name', $name)->where('guard_name', 'web')->first();
    }

    /**
     * Semua peran beserta jumlah pengguna dan hak aksesnya: peran bawaan dulu
     * (urutan Role::SYSTEM), lalu peran buatan sendiri menurut abjad.
     *
     * @return Collection<int, Role>
     */
    public function allWithCounts(): Collection
    {
        return $this->ordered(Role::query()->withCount(['users', 'permissions'])->get());
    }

    /**
     * Peran yang boleh dipasang ke akun karyawan: bukan superadmin (tidak pernah
     * bisa diberikan lewat form), bukan pelanggan (dikelola di Akun pelanggan).
     *
     * @return Collection<int, Role>
     */
    public function assignableToStaff(): Collection
    {
        return $this->ordered(
            Role::query()->whereNotIn('name', [Role::LOCKED, 'customer'])->where('guard_name', 'web')->get(),
        );
    }

    public function nameTaken(string $name, ?string $ignoreId = null): bool
    {
        return Role::query()
            ->whereRaw('lower(name) = ?', [mb_strtolower(trim($name))])
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    public function userCount(Role $role): int
    {
        return $role->users()->count();
    }

    /**
     * @return array<int, string>
     */
    public function permissionNames(Role $role): array
    {
        return $role->permissions()->pluck('name')->all();
    }

    /**
     * Saring nama yang benar-benar ada: syncPermissions() melempar
     * PermissionDoesNotExist untuk nama yang belum di-seed.
     *
     * @param  array<int, string>  $names
     * @return array<int, string>
     */
    public function existingPermissionNames(array $names): array
    {
        return Permission::query()
            ->whereIn('name', $names)
            ->where('guard_name', 'web')
            ->pluck('name')
            ->all();
    }

    public function create(string $name): Role
    {
        return Role::query()->create(['name' => $name, 'guard_name' => 'web']);
    }

    public function rename(Role $role, string $name): Role
    {
        $role->update(['name' => $name]);

        return $role;
    }

    /**
     * @param  array<int, string>  $names
     */
    public function syncPermissions(Role $role, array $names): void
    {
        $role->syncPermissions($names);
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }

    /**
     * @param  Collection<int, Role>  $roles
     * @return Collection<int, Role>
     */
    private function ordered(Collection $roles): Collection
    {
        return $roles
            ->sortBy(fn (Role $role): array => [
                $role->isSystem() ? (int) array_search($role->name, Role::SYSTEM, true) : 100,
                mb_strtolower((string) $role->name),
            ])
            ->values();
    }
}
