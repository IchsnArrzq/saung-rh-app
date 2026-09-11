<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpecialRequestCategory;
use Illuminate\View\View;

class SpecialRequestCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.special-request-categories.index');
    }

    public function create(): View
    {
        return view('admin.special-request-categories.create');
    }

    public function edit(SpecialRequestCategory $specialRequestCategory): View
    {
        return view('admin.special-request-categories.edit', [
            'specialRequestCategory' => $specialRequestCategory,
        ]);
    }
}
