<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $menus = Menu::query()
            ->with('category')
            ->latest()
            ->paginate(12);

        return view('admin.menus.index', compact('menus'));
    }

    public function create(): View
    {
        $categories = MenuCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.menus.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'menu_category_id' => ['nullable', 'exists:menu_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique('menus', 'slug')],
            'sku' => ['nullable', 'string', 'max:60', Rule::unique('menus', 'sku')],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['is_available'] = $request->boolean('is_available');

        Menu::create($validated);

        return redirect()->route('menus.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu): View
    {
        $categories = MenuCategory::query()
            ->where('is_active', true)
            ->orWhere('id', $menu->menu_category_id)
            ->orderBy('name')
            ->get();

        return view('admin.menus.edit', compact('menu', 'categories'));
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $validated = $request->validate([
            'menu_category_id' => ['nullable', 'exists:menu_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique('menus', 'slug')->ignore($menu->id)],
            'sku' => ['nullable', 'string', 'max:60', Rule::unique('menus', 'sku')->ignore($menu->id)],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['is_available'] = $request->boolean('is_available');

        $menu->update($validated);

        return redirect()->route('menus.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()->route('menus.index')->with('success', 'Menu berhasil dihapus.');
    }
}
