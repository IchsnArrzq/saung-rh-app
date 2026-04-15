<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Services\Admin\MenuCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuCategoryController extends Controller
{
    public function __construct(private readonly MenuCategoryService $menuCategoryService) {}

    public function index(): View
    {
        return view('admin.menu-categories.index', [
            'categories' => $this->menuCategoryService->paginate(),
        ]);
    }

    public function create(): View
    {
        return view('admin.menu-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->menuCategoryService->create($request);

        return redirect()->route('menu-categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(MenuCategory $menuCategory): View
    {
        return view('admin.menu-categories.edit', [
            'menuCategory' => $menuCategory,
        ]);
    }

    public function update(Request $request, MenuCategory $menuCategory): RedirectResponse
    {
        $this->menuCategoryService->update($request, $menuCategory);

        return redirect()->route('menu-categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(MenuCategory $menuCategory): RedirectResponse
    {
        $this->menuCategoryService->delete($menuCategory);

        return redirect()->route('menu-categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
