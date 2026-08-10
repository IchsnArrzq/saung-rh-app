<?php

namespace App\Http\Controllers;

use App\Domains\Menu\QueryUseCases\GetMenuCatalogQueryUseCase;
use App\Support\RestaurantCart;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function __invoke(GetMenuCatalogQueryUseCase $catalog): View
    {
        return view('public.home', [
            'menus' => $catalog->featured(),
            'cartCount' => RestaurantCart::count(),
        ]);
    }
}
