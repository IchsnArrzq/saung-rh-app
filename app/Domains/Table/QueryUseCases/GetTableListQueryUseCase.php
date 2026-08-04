<?php

namespace App\Domains\Table\QueryUseCases;

use App\Domains\Table\Repositories\TableRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GetTableListQueryUseCase
{
    public function __construct(private readonly TableRepositoryInterface $tables) {}

    public function paginate(string $search = '', int $perPage = 12): LengthAwarePaginator
    {
        return $this->tables->paginateForAdmin($perPage, $search);
    }

    /** Whole floor, for the status board and table map. */
    public function all(): Collection
    {
        return $this->tables->allOrdered();
    }

    /** Whole floor filtered by code/name — floor tools never paginate. */
    public function search(string $search = ''): Collection
    {
        return $this->tables->search($search);
    }

    /** Only free tables — customer picker and booking form. */
    public function selectable(): Collection
    {
        return $this->tables->selectable();
    }
}
