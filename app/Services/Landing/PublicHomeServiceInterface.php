<?php

namespace App\Services\Landing;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Collection;

interface PublicHomeServiceInterface
{
    /**
     * @return Collection<int, Menu>
     */
    public function featuredMenus(int $limit = 8): Collection;

    public function cartCount(): int;
}
