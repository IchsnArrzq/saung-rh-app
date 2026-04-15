<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Services\Landing\PublicCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class PublicMenuCartController extends Controller
{
    public function __construct(private readonly PublicCartService $publicCartService) {}

    public function store(Menu $menu): RedirectResponse
    {
        try {
            $this->publicCartService->quickAdd($menu);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', $menu->name.' berhasil dimasukkan ke cart.');
    }
}
