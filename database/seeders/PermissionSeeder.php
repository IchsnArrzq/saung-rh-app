<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'menus.manage',
            'menu-categories.manage',
            'tables.manage',
            'orders.manage',
            'payments.manage',
            'reservations.manage',
            'customer.booking.view',
            'customer.booking.create',
        ];

        foreach ($permissions as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $adminPermissions = Permission::query()
            ->whereIn('name', [
                'dashboard.view',
                'menus.manage',
                'menu-categories.manage',
                'tables.manage',
                'orders.manage',
                'payments.manage',
                'reservations.manage',
            ])
            ->pluck('name')
            ->all();

        $cashierPermissions = Permission::query()
            ->whereIn('name', [
                'dashboard.view',
                'orders.manage',
                'payments.manage',
            ])
            ->pluck('name')
            ->all();

        $customerPermissions = Permission::query()
            ->whereIn('name', ['customer.booking.view', 'customer.booking.create'])
            ->pluck('name')
            ->all();

        $superAdmin = Role::query()->where('name', 'superadmin')->first();
        $admin = Role::query()->where('name', 'admin')->first();
        $cashier = Role::query()->where('name', 'cashier')->first();
        $customer = Role::query()->where('name', 'customer')->first();

        if ($superAdmin) {
            $superAdmin->syncPermissions($permissions);
        }

        if ($admin) {
            $admin->syncPermissions($adminPermissions);
        }

        if ($cashier) {
            $cashier->syncPermissions($cashierPermissions);
        }

        if ($customer) {
            $customer->syncPermissions($customerPermissions);
        }
    }
}
