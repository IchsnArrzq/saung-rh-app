<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuStatus;
use Illuminate\View\View;

class MenuStatusController extends Controller
{
    public function index(): View
    {
        return view('admin.menu-statuses.index');
    }

    public function create(): View
    {
        return view('admin.menu-statuses.create');
    }

    public function edit(MenuStatus $menuStatus): View
    {
        return view('admin.menu-statuses.edit', [
            'menuStatus' => $menuStatus,
        ]);
    }
}
