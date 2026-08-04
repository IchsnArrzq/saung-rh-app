<?php

namespace Database\Seeders;

use App\Domains\Table\Enums\TableStatus;
use App\Models\Table;
use App\Models\TableCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statusOptions = [
            TableStatus::Available,
            TableStatus::Occupied,
            TableStatus::OrderIn,
            TableStatus::Cleaning,
        ];

        $categoryIds = TableCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('id')
            ->all();

        $columns = 5;

        for ($i = 1; $i <= 20; $i++) {
            $status = $statusOptions[array_rand($statusOptions)];

            Table::query()->updateOrCreate(
                ['code' => 'T-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT)],
                [
                    'name' => 'Meja '.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'capacity' => fake()->randomElement([2, 4, 4, 6, 8]),
                    'qr_token' => Str::random(24),
                    'status' => $status->value,
                    'table_category_id' => $categoryIds !== []
                        ? $categoryIds[array_rand($categoryIds)]
                        : null,
                    // Grid coordinates power the receptionist Table Map (5-column layout).
                    'position_x' => ($i - 1) % $columns,
                    'position_y' => intdiv($i - 1, $columns),
                    'notes' => fake()->boolean(20) ? fake()->sentence() : null,
                ]
            );
        }
    }
}
