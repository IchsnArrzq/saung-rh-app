<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TableCategory;
use Illuminate\View\View;

class TableCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', TableCategory::class);

        return view('admin.table-categories.index');
    }

    public function create(): View
    {
        $this->authorize('create', TableCategory::class);

        return view('admin.table-categories.create');
    }

    public function edit(TableCategory $tableCategory): View
    {
        $this->authorize('update', $tableCategory);

        return view('admin.table-categories.edit', [
            'tableCategory' => $tableCategory,
        ]);
    }
}
