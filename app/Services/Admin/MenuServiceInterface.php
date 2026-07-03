<?php

namespace App\Services\Admin;

use App\Models\Menu;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface MenuServiceInterface
{
    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator;

    public function categories(?Menu $menu = null): Collection;

    public function create(Request $request): Menu;

    public function update(Request $request, Menu $menu): void;

    public function delete(Menu $menu): void;
}
