<?php

namespace App\Services\Landing;

use App\Models\Menu;

interface PublicCartServiceInterface
{
    public function quickAdd(Menu $menu, int $qty = 1, ?string $notes = null): void;
}
