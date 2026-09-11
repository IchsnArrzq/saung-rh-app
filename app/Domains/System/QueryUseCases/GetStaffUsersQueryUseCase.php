<?php

namespace App\Domains\System\QueryUseCases;

use App\Domains\System\Repositories\RoleRepository;
use App\Domains\System\Repositories\UserRepository;
use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Layar Karyawan: daftar akun staf (semua peran selain pelanggan, termasuk
 * peran buatan sendiri) dan pilihan peran untuk form serta saringan.
 */
class GetStaffUsersQueryUseCase
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RoleRepository $roles,
    ) {}

    public function paginate(string $search = '', string $role = '', int $perPage = 15): LengthAwarePaginator
    {
        return $this->users->paginateStaff($search, $role, $perPage);
    }

    /**
     * Peran yang boleh dipasang lewat form: tanpa superadmin dan pelanggan.
     *
     * @return array<string, string> name => label
     */
    public function assignableRoleOptions(): array
    {
        return $this->roles->assignableToStaff()
            ->mapWithKeys(fn (Role $role) => [(string) $role->name => $role->label()])
            ->all();
    }

    /**
     * Saringan daftar: semua peran staf, termasuk superadmin (untuk menemukan
     * akunnya), tanpa pelanggan.
     *
     * @return array<string, string> name => label
     */
    public function filterRoleOptions(): array
    {
        return $this->roles->allWithCounts()
            ->reject(fn (Role $role) => $role->name === 'customer')
            ->mapWithKeys(fn (Role $role) => [(string) $role->name => $role->label()])
            ->all();
    }
}
