<?php

namespace App\Domains\Menu\QueryUseCases;

use App\Domains\Menu\Repositories\MenuRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Read-only catalog shared by the customer portal, the guest QR menu and the
 * landing page.
 */
class GetMenuCatalogQueryUseCase
{
    public function __construct(private readonly MenuRepositoryInterface $menus) {}

    public function paginate(string $search = '', ?string $categoryId = null, int $perPage = 12): LengthAwarePaginator
    {
        return $this->menus->paginateAvailable($search, $categoryId, $perPage);
    }

    /** Every sellable item — order screens that need the whole list. */
    public function available(): Collection
    {
        return $this->menus->available();
    }

    public function featured(int $limit = 8): Collection
    {
        return $this->menus->featured($limit);
    }
}
