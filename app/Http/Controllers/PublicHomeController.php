<?php

namespace App\Http\Controllers;

use App\Services\Landing\PublicHomeServiceInterface;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function __construct(private readonly PublicHomeServiceInterface $publicHomeService) {}

    public function __invoke(): View
    {
        return view('public.home', [
            'menus' => $this->publicHomeService->featuredMenus(),
            'cartCount' => $this->publicHomeService->cartCount(),
        ]);
    }
}
