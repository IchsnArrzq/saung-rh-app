<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menuCollection = Menu::query()->where('is_available', true)->get();

        if ($menuCollection->isEmpty()) {
            return;
        }

        Order::query()->chunk(50, function ($orders) use ($menuCollection): void {
            foreach ($orders as $order) {
                if ($order->items()->exists()) {
                    continue;
                }

                $chosenMenus = $menuCollection->random(rand(1, min(4, $menuCollection->count())));

                $items = collect($chosenMenus)->map(function ($menu): array {
                    $qty = rand(1, 4);
                    $price = (float) $menu->price;

                    return [
                        'menu_id' => $menu->id,
                        'menu_name_snapshot' => $menu->name,
                        'qty' => $qty,
                        'price' => $price,
                        'line_total' => $qty * $price,
                        'notes' => fake()->boolean(20) ? fake()->sentence(4) : null,
                    ];
                })->values()->all();

                $order->items()->createMany($items);

                $subtotal = $order->items()->sum('line_total');
                $tax = (float) round($subtotal * 0.11, 2);
                $total = max($subtotal + $tax, 0);

                $order->update([
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'tax' => $tax,
                    'total' => $total,
                ]);
            }
        });
    }
}
