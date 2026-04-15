<?php

namespace App\Http\Controllers;

use App\Services\Landing\PublicHomeService;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function __construct(private readonly PublicHomeService $publicHomeService) {}

    public function __invoke(): View
    {
        return view('public.home', [
            'menus' => $this->publicHomeService->featuredMenus(),
            'cartCount' => $this->publicHomeService->cartCount(),
        ]);
    }
}
