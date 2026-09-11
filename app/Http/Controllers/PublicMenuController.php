<?php

namespace App\Http\Controllers;

use App\Domains\Table\QueryUseCases\GetTableSessionQueryUseCase;
use App\Models\Menu;
use App\Support\RestaurantCart;
use App\Support\TableSessionContext;
use Illuminate\Http\Request;

class PublicMenuController extends Controller
{
    /**
     * The catalog, plus the table panel when this phone's QR session is live.
     */
    public function __invoke(Request $request, GetTableSessionQueryUseCase $sessions)
    {
        $tableSession = $sessions->live(TableSessionContext::sessionId());

        // A phone still carrying a finished session is no longer at that
        // table: forget it, so the menu stops presenting itself as the table's.
        if (! $tableSession) {
            TableSessionContext::clear();
        }

        return view('public.menu', ['tableSession' => $tableSession]);
    }

    public function show(Request $request, Menu $menu)
    {
        $context = RestaurantCart::syncContextFromRequest($request);

        // `status` is a plain column on `menus` (backed by MenuAvailability),
        // not a relationship -- eager-loading it threw on every request. The
        // media relations are what the page actually reads: `images` also backs
        // `display_image_url` via primaryImage().
        $menu->loadMissing(['category', 'images', 'videos']);

        $relatedMenus = Menu::query()
            ->with('category')
            ->available()
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
