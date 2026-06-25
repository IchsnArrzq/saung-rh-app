<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            TableStatusSeeder::class,
            TableCategorySeeder::class,
            TableSeeder::class,
            MenuCategorySeeder::class,
            MenuStatusSeeder::class,
            MenuSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            PaymentSeeder::class,
            ReservationSeeder::class,
            ReservationItemSeeder::class,
            Fase3DemoSeeder::class,
            Fase5DemoSeeder::class,
            Fase6DemoSeeder::class,
            SystemSettingsSeeder::class,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
