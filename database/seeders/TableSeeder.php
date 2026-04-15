<?php

namespace Database\Seeders;

use App\Models\Table;
use App\Models\TableCategory;
use App\Models\TableStatus;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statusOptions = ['available', 'occupied', 'order_in', 'cleaning'];
        $statusMap = TableStatus::query()
            ->whereIn('key', $statusOptions)
            ->get()
            ->keyBy('key');
        $defaultStatus = TableStatus::query()->where('is_default', true)->first()
            ?? TableStatus::query()->orderBy('sort_order')->orderBy('name')->first();

        $categoryIds = TableCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('id')
            ->all();

        for ($i = 1; $i <= 20; $i++) {
            $statusKey = $statusOptions[array_rand($statusOptions)];
            $status = $statusMap->get($statusKey) ?: $defaultStatus;

            Table::query()->updateOrCreate(
                ['code' => 'T-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT)],
                [
                    'name' => 'Meja '.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'capacity' => fake()->randomElement([2, 4, 4, 6, 8]),
                    'status' => $status?->key ?? 'available',
                    'table_status_id' => $status?->id,
                    'table_category_id' => $categoryIds !== []
                        ? $categoryIds[array_rand($categoryIds)]
                        : null,
                    'notes' => fake()->boolean(20) ? fake()->sentence() : null,
                ]
            );
        }
    }
}
