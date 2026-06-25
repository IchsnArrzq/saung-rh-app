<?php

namespace Database\Seeders;

use App\Models\TableStatus;
use Illuminate\Database\Seeder;

class TableStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['key' => 'available', 'name' => 'Tersedia', 'color' => 'success', 'sort_order' => 1, 'is_active' => true, 'is_default' => true],
            ['key' => 'occupied', 'name' => 'Terisi', 'color' => 'error', 'sort_order' => 2, 'is_active' => true, 'is_default' => false],
            ['key' => 'order_in', 'name' => 'Pesanan Masuk', 'color' => 'warning', 'sort_order' => 3, 'is_active' => true, 'is_default' => false],
            ['key' => 'reserved', 'name' => 'Direservasi', 'color' => 'secondary', 'sort_order' => 4, 'is_active' => true, 'is_default' => false],
            ['key' => 'cleaning', 'name' => 'Perlu Dibersihkan', 'color' => 'info', 'sort_order' => 5, 'is_active' => true, 'is_default' => false],
        ];

        foreach ($statuses as $status) {
            TableStatus::query()->updateOrCreate(
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

        TableStatus::query()
            ->where('key', '!=', 'available')
            ->update(['is_default' => false]);

        TableStatus::query()
            ->where('key', 'available')
            ->update(['is_default' => true]);
    }
}
