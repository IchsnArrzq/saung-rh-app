<?php

namespace App\Domains\Menu\Repositories;

use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class MenuRepository
{
    public function find(string $id): ?Menu
    {
        return Menu::query()->find($id);
    }

    /** One item with its category loaded — the menu detail modal. */
    public function findWithCategory(string $id): ?Menu
    {
        return Menu::query()->with('category:id,name')->find($id);
    }

    /**
     * Several items in one query, keyed by id — for snapshotting a basket of
     * prices without an N+1.
     *
     * @param  array<int, string>  $ids
     * @return Collection<string, Menu>
     */
    public function findManyKeyedById(array $ids): Collection
    {
        return Menu::query()->whereIn('id', $ids)->get()->keyBy('id');
    }

    /**
     * Active categories with a count of their sellable items. Every order
     * screen shows the same chip row, so the query lives here once.
     *
     * @return Collection<int, MenuCategory>
     */
    public function activeCategoriesWithAvailableCounts(): Collection
    {
        return MenuCategory::query()
            ->where('is_active', true)
            ->withCount(['menus' => fn (Builder $query) => $query->available()])
            ->orderBy('name')
            ->get();
    }

    /** Sellable items only, for catalogs and order screens. */
    public function available(): Collection
    {
        return Menu::query()
            ->with('category:id,name')
            ->available()
            ->orderBy('name')
            ->get();
    }

    /** Sellable items narrowed by category and free text — the POS order pad shows them all at once. */
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

    /** Paginated catalog for customer/guest browsing. */
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

    /** Admin listing across every availability state. */
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

    /** A short list for the landing page. */
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
