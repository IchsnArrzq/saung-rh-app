<?php

namespace App\Domains\Menu\Repositories;

use App\Models\Menu;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class MenuRepository implements MenuRepositoryInterface
{
    public function find(string $id): ?Menu
    {
        return Menu::query()->find($id);
    }

    public function available(): Collection
    {
        return Menu::query()
            ->available()
            ->orderBy('name')
            ->get();
    }

    public function paginateAvailable(string $search = '', ?string $categoryId = null, int $perPage = 12): LengthAwarePaginator
    {
        $search = trim($search);

        return Menu::query()
            ->with('category:id,name')
            ->available()
            ->when($categoryId, fn (Builder $query) => $query->where('menu_category_id', $categoryId))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateForAdmin(int $perPage = 12, string $search = ''): LengthAwarePaginator
    {
        $search = trim($search);

        return Menu::query()
            ->with('category:id,name')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function featured(int $limit = 8): Collection
    {
        return Menu::query()
            ->with('category:id,name')
            ->available()
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function countAvailable(): int
    {
        return Menu::query()->available()->count();
    }

    public function countUnavailable(): int
    {
        return Menu::query()->unavailable()->count();
    }
}
