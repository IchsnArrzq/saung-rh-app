<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Support\RestaurantCart;
use Illuminate\Http\Request;

class PublicMenuController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return view('public.menu');
    }

    public function show(Request $request, Menu $menu)
    {
        $context = RestaurantCart::syncContextFromRequest($request);

        $menu->loadMissing(['category', 'status']);

        $relatedMenus = Menu::query()
            ->with('category')
            ->where('is_available', true)
            ->whereKeyNot($menu->id)
            ->when($menu->menu_category_id, fn ($query) => $query->where('menu_category_id', $menu->menu_category_id))
            ->orderBy('name')
            ->limit(4)
            ->get();

        return view('public.menu-show', [
            'menu' => $menu,
            'mode' => $context['mode'],
            'tableId' => $context['table_id'],
            'relatedMenus' => $relatedMenus,
        ]);
    }
}
