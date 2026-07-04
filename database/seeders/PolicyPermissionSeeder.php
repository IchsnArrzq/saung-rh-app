<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\PolicyPermissions;
use Illuminate\Database\Seeder;

class PolicyPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = PolicyPermissions::names();

        foreach ($names as $name) {
            Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $superadmin = Role::query()->where('name', 'superadmin')->first();

        if ($superadmin) {
            $superadmin->givePermissionTo($names);
        }
    }
}
