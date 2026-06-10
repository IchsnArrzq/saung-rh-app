<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['superadmin', 'admin', 'cashier', 'customer'] as $roleName) {
            Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }
    }
}
