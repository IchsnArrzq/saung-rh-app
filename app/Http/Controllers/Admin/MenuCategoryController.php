<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\View\View;

class MenuCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', MenuCategory::class);

        return view('admin.menu-categories.index');
    }

    public function create(): View
    {
        $this->authorize('create', MenuCategory::class);

        return view('admin.menu-categories.create');
    }

    public function edit(MenuCategory $menuCategory): View
    {
        $this->authorize('update', $menuCategory);

        return view('admin.menu-categories.edit', [
            'menuCategory' => $menuCategory,
        ]);
    }
}
