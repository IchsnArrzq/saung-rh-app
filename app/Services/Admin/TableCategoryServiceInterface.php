<?php

namespace App\Services\Admin;

use App\Models\TableCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface TableCategoryServiceInterface
{
    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator;

    public function create(Request $request): TableCategory;

    public function update(Request $request, TableCategory $tableCategory): void;

    public function delete(TableCategory $tableCategory): void;
}
