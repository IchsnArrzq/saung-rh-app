<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\Admin\MenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __construct(private readonly MenuService $menuService) {}

    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());

        return view('admin.menus.index', [
            'menus' => $this->menuService->paginate(search: $search),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.menus.create', [
            'categories' => $this->menuService->categories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->menuService->create($request);

        return redirect()->route('menus.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu): View
    {
        return view('admin.menus.edit', [
            'menu' => $menu,
            'categories' => $this->menuService->categories($menu),
        ]);
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $this->menuService->update($request, $menu);

        return redirect()->route('menus.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $this->menuService->delete($menu);

        return redirect()->route('menus.index')->with('success', 'Menu berhasil dihapus.');
    }
}
