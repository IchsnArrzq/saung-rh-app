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
            'name' => 'Admin Saung RH',
            'password' => Hash::make('password'),
        ]);
        $admin->syncRoles(['admin']);

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
