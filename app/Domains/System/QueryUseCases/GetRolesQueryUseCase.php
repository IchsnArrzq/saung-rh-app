<?php

namespace App\Domains\System\QueryUseCases;

use App\Domains\System\Repositories\RoleRepository;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

/**
 * Daftar peran untuk layar Peran & hak akses, dan pilihan peran untuk form
 * karyawan.
 */
class GetRolesQueryUseCase
{
    public function __construct(private readonly RoleRepository $roles) {}

    /**
     * @return Collection<int, Role>
     */
    public function list(): Collection
    {
        return $this->roles->allWithCounts();
    }

    public function find(string $id): ?Role
    {
        return $this->roles->find($id);
    }

    /**
     * @return array<int, string>
     */
    public function permissionNamesOf(Role $role): array
    {
        return $this->roles->permissionNames($role);
    }

    /**
     * name => label, bentuk yang dibaca <x-select>.
     *
     * @return array<string, string>
     */
    public function staffRoleOptions(): array
    {
        return $this->roles->assignableToStaff()
            ->mapWithKeys(fn (Role $role) => [(string) $role->name => $role->label()])
            ->all();
    }
}
