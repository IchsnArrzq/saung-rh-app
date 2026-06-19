<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Menu;
use Illuminate\View\View;

class IngredientController extends Controller
{
    public function index(): View
    {
        return view('admin.ingredients.index');
    }

    public function create(): View
    {
        return view('admin.ingredients.create');
    }

    public function edit(Ingredient $ingredient): View
    {
        return view('admin.ingredients.edit', [
            'ingredient' => $ingredient,
        ]);
    }

    public function menuIngredients(Menu $menu): View
    {
        return view('admin.ingredients.menu-ingredients', [
            'menu' => $menu,
        ]);
    }
}
