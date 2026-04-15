<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Reservation;
use Illuminate\Database\Seeder;

class ReservationItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = Menu::query()->where('is_available', true)->get();

        if ($menus->isEmpty()) {
            return;
        }

        Reservation::query()->chunk(50, function ($reservations) use ($menus): void {
            foreach ($reservations as $reservation) {
                if ($reservation->items()->exists()) {
                    continue;
                }

                $selectedMenus = $menus->random(rand(1, min(3, $menus->count())));

                $items = collect($selectedMenus)->map(function ($menu): array {
                    $qty = rand(1, 3);
                    $unitPrice = (float) $menu->price;

                    return [
                        'menu_id' => $menu->id,
                        'menu_name_snapshot' => $menu->name,
                        'qty' => $qty,
                        'unit_price' => $unitPrice,
                        'line_total' => $qty * $unitPrice,
                        'notes' => fake()->boolean(20) ? fake()->sentence(5) : null,
                    ];
                })->values()->all();

                $reservation->items()->createMany($items);
            }
        });
    }
}
