<?php

namespace App\Domains\Menu\QueryUseCases;

use App\Domains\Menu\Repositories\MenuRepository;
use App\Models\Menu;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Read-only catalog shared by the customer portal, the guest QR menu and the
 * landing page.
 */
class GetMenuCatalogQueryUseCase
{
    public function __construct(private readonly MenuRepository $menus) {}

    public function paginate(string $search = '', ?string $categoryId = null, int $perPage = 12): LengthAwarePaginator
    {
        return $this->menus->paginateAvailable($search, $categoryId, $perPage);
    }

    /** Every sellable item — order screens that need the whole list. */
    public function available(): Collection
    {
        return $this->menus->available();
    }

    /** The whole sellable list, narrowed — the POS pad filters without paging. */
    public function availableFiltered(string $search = '', int|string|null $categoryId = null): Collection
    {
        return $this->menus->availableFiltered($search, $categoryId);
    }

    public function countAvailable(): int
    {
        return $this->menus->countAvailable();
    }

    /** Category chips with their sellable-item counts. */
    public function categories(): Collection
    {
        return $this->menus->activeCategoriesWithAvailableCounts();
    }

    /** One item with its category — the detail modal on the order screens. */
    public function find(string $id): ?Menu
    {
        return $this->menus->findWithCategory($id);
    }

    public function featured(int $limit = 8): Collection
    {
        return $this->menus->featured($limit);
    }
}
