<?php

namespace App\Domains\Menu\Repositories;

use App\Models\Menu;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface MenuRepositoryInterface
{
    public function find(string $id): ?Menu;

    /** Sellable items only, for catalogs and order screens. */
    public function available(): Collection;

    /** Paginated catalog for customer/guest browsing. */
    public function paginateAvailable(string $search = '', ?string $categoryId = null, int $perPage = 12): LengthAwarePaginator;

    /** Admin listing across every availability state. */
    public function paginateForAdmin(int $perPage = 12, string $search = ''): LengthAwarePaginator;

    /** A short list for the landing page. */
    public function featured(int $limit = 8): Collection;

    public function countAvailable(): int;

    public function countUnavailable(): int;
}
