<?php

namespace App\Services\Admin;

use App\Models\TableStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface TableStatusServiceInterface
{
    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator;

    public function create(Request $request): TableStatus;

    public function update(Request $request, TableStatus $tableStatus): void;

    public function delete(TableStatus $tableStatus): void;
}
