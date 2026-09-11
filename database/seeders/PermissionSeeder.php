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
            'tables.status.update',
            'orders.manage',
            'payments.manage',
            'reservations.manage',
            'pos.manage',
            'kitchen.view',
            'receptionist.monitor',
            'waiter.operate',
            'manager.dashboard',
            'reports.view',
            'users.manage',
            'customer.booking.view',
            'customer.booking.create',
            // Gerbang kasar area /admin untuk role buatan layar Peran & hak
            // akses (routes/admin.php). Role bawaan tetap lolos lewat namanya.
            'backoffice.access',
            // Membalas & membersihkan obrolan meja dari Panel meja (TablePolicy::moderateChat).
            'table_chat.moderate',
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
                'pos.manage',
            ])
            ->pluck('name')
            ->all();

        $cashierPermissions = Permission::query()
            ->whereIn('name', [
                'dashboard.view',
                'orders.manage',
                'payments.manage',
                'pos.manage',
            ])
            ->pluck('name')
            ->all();

        $managerPermissions = [
            'dashboard.view',
            'manager.dashboard',
            'reports.view',
            'reservations.manage',
            'receptionist.monitor',
        ];

        $receptionistPermissions = [
            'dashboard.view',
            'receptionist.monitor',
            'reservations.manage',
            'tables.manage',
            'tables.status.update',
            'kitchen.view',
        ];

        $waiterPermissions = [
            'dashboard.view',
            'waiter.operate',
            'tables.status.update',
        ];

        $chefPermissions = [
            'dashboard.view',
            'kitchen.view',
        ];

        $obPermissions = [
            'dashboard.view',
            'tables.status.update',
        ];

        $customerPermissions = Permission::query()
            ->whereIn('name', ['customer.booking.view', 'customer.booking.create'])
            ->pluck('name')
            ->all();

        $assignments = [
            'superadmin' => $permissions,
            'admin' => array_merge($adminPermissions, [
                'kitchen.view', 'reports.view', 'users.manage', 'tables.status.update',
                'backoffice.access', 'table_chat.moderate',
            ]),
            'manager' => array_merge($managerPermissions, ['table_chat.moderate']),
            'receptionist' => array_merge($receptionistPermissions, ['table_chat.moderate']),
            'cashier' => array_merge($cashierPermissions, ['backoffice.access', 'table_chat.moderate']),
            'waiter' => array_merge($waiterPermissions, ['table_chat.moderate']),
            'chef' => $chefPermissions,
            'ob' => $obPermissions,
            'customer' => $customerPermissions,
        ];

        foreach ($assignments as $roleName => $rolePermissions) {
            $role = Role::query()->where('name', $roleName)->first();

            if ($role) {
                $role->syncPermissions($rolePermissions);
            }
        }
    }
}
