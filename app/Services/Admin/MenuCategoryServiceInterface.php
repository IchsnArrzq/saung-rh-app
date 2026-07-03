<?php

namespace App\Services\Admin;

use App\Models\MenuCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface MenuCategoryServiceInterface
{
    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator;

    public function create(Request $request): MenuCategory;

    public function update(Request $request, MenuCategory $menuCategory): void;

    public function delete(MenuCategory $menuCategory): void;
}
