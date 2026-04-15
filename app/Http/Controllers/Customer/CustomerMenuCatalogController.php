<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\MenuCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerMenuCatalogController extends Controller
{
    public function __construct(private readonly MenuCatalogService $menuCatalogService) {}

    public function __invoke(Request $request): View
    {
        $search = trim($request->string('search')->toString());

        return view('customer.menus.index', [
            'search' => $search,
            'menus' => $this->menuCatalogService->paginateAvailable($search),
        ]);
    }
}
