<?php

namespace App\Services\Customer;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MenuCatalogServiceInterface
{
    public function paginateAvailable(string $search = '', int $perPage = 12): LengthAwarePaginator;
}
