<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::query()->firstOrCreate([
            'email' => 'superadmin@example.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
        ]);
        $superAdmin->syncRoles(['superadmin']);

        $admin = User::query()->firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin',
            'password' => Hash::make('password'),
        ]);
        $admin->syncRoles(['admin']);

        $cashier = User::query()->firstOrCreate([
            'email' => 'cashier@example.com',
        ], [
            'name' => 'Kasir',
            'password' => Hash::make('password'),
        ]);
        $cashier->syncRoles(['cashier']);

        // Named for the role, not the restaurant: the business name lives in
        // app settings now (see BusinessProfile), and baking a copy of it into
        // demo accounts only creates a second place to rename.
        $staffRoles = [
            'manager' => 'Manager',
            'receptionist' => 'Resepsionis',
            'waiter' => 'Waiter',
            'chef' => 'Chef',
            'ob' => 'Office Boy',
        ];

        foreach ($staffRoles as $role => $name) {
            $staff = User::query()->firstOrCreate([
                'email' => "{$role}@example.com",
            ], [
                'name' => $name,
                'password' => Hash::make('password'),
            ]);
            $staff->syncRoles([$role]);
        }

        User::query()->firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => Hash::make('password'),
        ])->syncRoles(['superadmin']);

        for ($i = 1; $i <= 10; $i++) {
            $user = User::query()->firstOrCreate([
                'email' => "customer{$i}@example.com",
            ], [
                'name' => "Customer {$i}",
                'password' => Hash::make('password'),
            ]);

            $user->syncRoles(['customer']);
        }
    }
}
