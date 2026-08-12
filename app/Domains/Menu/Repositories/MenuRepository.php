<?php

namespace App\Domains\Menu\Repositories;

use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class MenuRepository implements MenuRepositoryInterface
{
    public function find(string $id): ?Menu
    {
        return Menu::query()->find($id);
    }

    public function findWithCategory(string $id): ?Menu
    {
        return Menu::query()->with('category:id,name')->find($id);
    }

    public function findManyKeyedById(array $ids): Collection
    {
        return Menu::query()->whereIn('id', $ids)->get()->keyBy('id');
    }

    public function activeCategoriesWithAvailableCounts(): Collection
    {
        return MenuCategory::query()
            ->where('is_active', true)
            ->withCount(['menus' => fn (Builder $query) => $query->available()])
            ->orderBy('name')
            ->get();
    }

    public function available(): Collection
    {
        return Menu::query()
            ->with('category:id,name')
            ->available()
            ->orderBy('name')
            ->get();
    }

    public function availableFiltered(string $search = '', int|string|null $categoryId = null): Collection
    {
        $search = trim($search);

        return Menu::query()
            ->with('category:id,name')
            ->available()
            ->when($categoryId, fn (Builder $query) => $query->where('menu_category_id', $categoryId))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'ilike', '%'.$search.'%')
                        ->orWhere('description', 'ilike', '%'.$search.'%')
                        ->orWhere('sku', 'ilike', '%'.$search.'%')
                        ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'ilike', '%'.$search.'%'));
                });
            })
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
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', '%'.$search.'%'));
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

    public function countAll(): int
    {
        return Menu::query()->count();
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
