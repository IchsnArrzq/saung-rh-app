<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Menu::class);

        return view('admin.menus.index');
    }

    public function create(): View
    {
        $this->authorize('create', Menu::class);

        return view('admin.menus.create');
    }

    public function edit(Menu $menu): View
    {
        $this->authorize('update', $menu);

        return view('admin.menus.edit', [
            'menu' => $menu,
        ]);
    }
}
