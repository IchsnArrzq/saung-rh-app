<?php

namespace App\Services\Admin;

use App\Models\Table;
use App\Models\TableCategory;
use App\Models\TableStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface TableServiceInterface
{
    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator;

    /**
     * @return Collection<int, TableStatus>
     */
    public function statusOptions(?Table $table = null): Collection;

    /**
     * @return Collection<int, TableStatus>
     */
    public function boardStatuses(): Collection;

    /**
     * @return Collection<int, TableCategory>
     */
    public function categoryOptions(?Table $table = null): Collection;

    public function create(Request $request): Table;

    public function update(Request $request, Table $table): void;

    public function delete(Table $table): void;

    public function updateStatus(Table $table, string $statusId): void;
}
