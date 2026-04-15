<?php

namespace App\Services\Landing;

use App\Models\Menu;
use App\Support\RestaurantCart;
use Illuminate\Validation\ValidationException;

class PublicCartService
{
    public function quickAdd(Menu $menu): void
    {
        if (! $menu->is_available) {
            throw ValidationException::withMessages([
                'menu' => 'Menu sedang tidak tersedia.',
            ]);
        }

        RestaurantCart::addItem($menu, 1);
    }
}
