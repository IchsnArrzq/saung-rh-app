<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuCategoryController extends Controller
{
    public function index(): View
    {
        $categories = MenuCategory::latest()->paginate(12);

        return view('admin.menu-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.menu-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique('menu_categories', 'slug')],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        MenuCategory::create($validated);

        return redirect()->route('menu-categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(MenuCategory $menuCategory): View
    {
        return view('admin.menu-categories.edit', compact('menuCategory'));
    }

    public function update(Request $request, MenuCategory $menuCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique('menu_categories', 'slug')->ignore($menuCategory->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        $menuCategory->update($validated);

        return redirect()->route('menu-categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(MenuCategory $menuCategory): RedirectResponse
    {
        $menuCategory->delete();

        return redirect()->route('menu-categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
