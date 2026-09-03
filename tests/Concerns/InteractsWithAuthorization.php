<?php

namespace Tests\Concerns;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

/**
 * Membuat user beserta role dan permission-nya untuk test yang menembus Policy.
 *
 * Roles & permissions dibuat seperlunya, bukan lewat seeder penuh: menanam 252
 * permission di setiap test membuat suite jauh lebih lambat tanpa menambah apa
 * pun yang diuji.
 */
trait InteractsWithAuthorization
{
    /**
     * User tanpa batas — lewat Gate::before, jadi tidak perlu permission satu
     * pun. Ini jalur termurah untuk test yang tidak sedang menguji otorisasi.
     */
    protected function actingAsSuperadmin(): User
    {
        return $this->actingAsRole('superadmin');
    }

    /**
     * User dengan role bernama dan tepat permission yang disebutkan.
     *
     * @param  array<int, string>  $permissions  nama "{model}.{ability}"
     */
    protected function actingAsRole(string $roleName, array $permissions = []): User
    {
        $role = Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

        foreach ($permissions as $name) {
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role->givePermissionTo($permissions);

        $user = User::factory()->create();
        $user->assignRole($role);

        // Spatie meng-cache tabel permission per proses; tanpa ini permission
        // yang baru dibuat di atas tidak terlihat oleh pengecekan berikutnya.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($user);

        return $user;
    }
}
