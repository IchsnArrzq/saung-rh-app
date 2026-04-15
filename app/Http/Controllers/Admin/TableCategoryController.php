<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TableCategory;
use App\Services\Admin\TableCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableCategoryController extends Controller
{
    public function __construct(private readonly TableCategoryService $tableCategoryService) {}

    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());

        return view('admin.table-categories.index', [
            'tableCategories' => $this->tableCategoryService->paginate(search: $search),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.table-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->tableCategoryService->create($request);

        return redirect()->route('table-categories.index')->with('success', 'Kategori meja berhasil ditambahkan.');
    }

    public function edit(TableCategory $tableCategory): View
    {
        return view('admin.table-categories.edit', [
            'tableCategory' => $tableCategory,
        ]);
    }

    public function update(Request $request, TableCategory $tableCategory): RedirectResponse
    {
        $this->tableCategoryService->update($request, $tableCategory);

        return redirect()->route('table-categories.index')->with('success', 'Kategori meja berhasil diperbarui.');
    }

    public function destroy(TableCategory $tableCategory): RedirectResponse
    {
        $this->tableCategoryService->delete($tableCategory);

        return redirect()->route('table-categories.index')->with('success', 'Kategori meja berhasil dihapus.');
    }
}
