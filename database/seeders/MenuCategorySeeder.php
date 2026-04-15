<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Makanan Utama',
            'Makanan Ringan',
            'Minuman Dingin',
            'Minuman Panas',
            'Dessert',
        ];

        foreach ($categories as $categoryName) {
            MenuCategory::query()->updateOrCreate(
                ['slug' => Str::slug($categoryName)],
                [
                    'name' => $categoryName,
                    'description' => fake()->sentence(10),
                    'is_active' => true,
                ]
            );
        }
    }
}
