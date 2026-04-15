<?php

namespace Database\Seeders;

use App\Models\TableCategory;
use Illuminate\Database\Seeder;

class TableCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Indoor', 'slug' => 'indoor', 'description' => 'Area dalam ruangan', 'sort_order' => 1, 'is_active' => true],
            ['name' => 'Outdoor', 'slug' => 'outdoor', 'description' => 'Area luar ruangan', 'sort_order' => 2, 'is_active' => true],
            ['name' => 'VIP', 'slug' => 'vip', 'description' => 'Area privat atau prioritas', 'sort_order' => 3, 'is_active' => true],
        ];

        foreach ($categories as $category) {
            TableCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => $category['is_active'],
                ]
            );
        }
    }
}
