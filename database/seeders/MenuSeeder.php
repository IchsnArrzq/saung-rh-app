<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            ['Nasi Goreng Special', 'Makanan Utama', 35000],
            ['Mie Goreng Jawa', 'Makanan Utama', 32000],
            ['Sate Ayam Madura', 'Makanan Utama', 38000],
            ['Ayam Bakar Madu', 'Makanan Utama', 42000],
            ['Kentang Goreng', 'Makanan Ringan', 18000],
            ['Tahu Crispy', 'Makanan Ringan', 16000],
            ['Pisang Coklat', 'Dessert', 22000],
            ['Es Teh Manis', 'Minuman Dingin', 10000],
            ['Es Jeruk', 'Minuman Dingin', 12000],
            ['Kopi Hitam', 'Minuman Panas', 15000],
            ['Cappuccino', 'Minuman Panas', 22000],
            ['Chocolate Lava', 'Dessert', 28000],
        ];

        foreach ($menus as [$menuName, $categoryName, $price]) {
            $category = MenuCategory::query()
                ->where('name', $categoryName)
                ->first();

            $slug = Str::slug($menuName);

            Menu::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'menu_category_id' => $category?->id,
                    'name' => $menuName,
                    'sku' => strtoupper(Str::slug($menuName, '')),
                    'description' => fake()->sentence(12),
                    'price' => $price,
                    'image_url' => 'https://picsum.photos/seed/'.$slug.'/640/480',
                    'is_available' => fake()->boolean(90),
                ]
            );
        }
    }
}
