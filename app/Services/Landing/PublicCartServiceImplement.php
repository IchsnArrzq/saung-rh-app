<?php

namespace App\Services\Landing;

use App\Models\Menu;
use App\Support\RestaurantCart;
use Illuminate\Validation\ValidationException;

class PublicCartServiceImplement implements PublicCartServiceInterface
{
    public function quickAdd(Menu $menu, int $qty = 1, ?string $notes = null): void
    {
        if (! $menu->is_available) {
            throw ValidationException::withMessages([
                'menu' => 'Menu sedang tidak tersedia.',
            ]);
        }

        RestaurantCart::addItem($menu, $qty, $notes);
    }
}
