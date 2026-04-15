<?php

namespace App\Services\Landing;

use App\Models\Menu;
use App\Support\RestaurantCart;
use Illuminate\Database\Eloquent\Collection;

class PublicHomeService
{
    /**
     * @return Collection<int, Menu>
     */
    public function featuredMenus(int $limit = 8): Collection
    {
        return Menu::query()
            ->with('category')
            ->where('is_available', true)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function cartCount(): int
    {
        return RestaurantCart::count();
    }
}
