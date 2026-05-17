<?php

namespace Database\Seeders;

use App\Models\MenuStatus;
use Illuminate\Database\Seeder;

class MenuStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['key' => 'available', 'name' => 'Tersedia', 'color' => 'success', 'sort_order' => 1, 'is_active' => true, 'is_default' => true],
            ['key' => 'unavailable', 'name' => 'Tidak Tersedia', 'color' => 'error', 'sort_order' => 2, 'is_active' => true, 'is_default' => false],
            ['key' => 'sold_out', 'name' => 'Habis', 'color' => 'warning', 'sort_order' => 3, 'is_active' => true, 'is_default' => false],
            ['key' => 'seasonal', 'name' => 'Musiman', 'color' => 'info', 'sort_order' => 4, 'is_active' => true, 'is_default' => false],
        ];

        foreach ($statuses as $status) {
            MenuStatus::query()->updateOrCreate(
                ['key' => $status['key']],
                [
                    'name' => $status['name'],
                    'color' => $status['color'],
                    'sort_order' => $status['sort_order'],
                    'is_active' => $status['is_active'],
                    'is_default' => $status['is_default'],
                ]
            );
        }

        MenuStatus::query()
            ->where('key', '!=', 'available')
            ->update(['is_default' => false]);

        MenuStatus::query()
            ->where('key', 'available')
            ->update(['is_default' => true]);
    }
}
