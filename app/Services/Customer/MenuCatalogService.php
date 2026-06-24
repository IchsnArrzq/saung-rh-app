<?php

namespace App\Services\Customer;

use App\Models\Menu;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MenuCatalogService
{
    public function paginateAvailable(string $search = '', int $perPage = 12): LengthAwarePaginator
    {
        $search = trim($search);

        return Menu::query()
            ->with('category')
            ->available()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }
}
