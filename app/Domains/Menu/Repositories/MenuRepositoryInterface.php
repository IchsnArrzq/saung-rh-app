<?php

namespace App\Domains\Menu\Repositories;

use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface MenuRepositoryInterface
{
    public function find(string $id): ?Menu;

    /** One item with its category loaded — the menu detail modal. */
    public function findWithCategory(string $id): ?Menu;

    /**
     * Several items in one query, keyed by id — for snapshotting a basket of
     * prices without an N+1.
     *
     * @param  array<int, string>  $ids
     * @return Collection<string, Menu>
     */
    public function findManyKeyedById(array $ids): Collection;

    /**
     * Active categories with a count of their sellable items. Every order
     * screen shows the same chip row, so the query lives here once.
     *
     * @return Collection<int, MenuCategory>
     */
    public function activeCategoriesWithAvailableCounts(): Collection;

    /** Sellable items only, for catalogs and order screens. */
    public function available(): Collection;

    /** Sellable items narrowed by category and free text — the POS order pad shows them all at once. */
    public function availableFiltered(string $search = '', int|string|null $categoryId = null): Collection;

    /** Paginated catalog for customer/guest browsing. */
    public function paginateAvailable(string $search = '', ?string $categoryId = null, int $perPage = 12): LengthAwarePaginator;

    /** Admin listing across every availability state. */
    public function paginateForAdmin(int $perPage = 12, string $search = ''): LengthAwarePaginator;

    /** A short list for the landing page. */
    public function featured(int $limit = 8): Collection;

    public function countAll(): int;

    public function countAvailable(): int;

    public function countUnavailable(): int;
}
