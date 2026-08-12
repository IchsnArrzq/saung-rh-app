<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Support\RestaurantCart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicMenuCartController extends Controller
{
    public function store(Request $request, Menu $menu): RedirectResponse
    {
        $validated = $request->validate([
            'qty' => ['nullable', 'integer', 'min:1', 'max:20'],
            'notes' => ['nullable', 'string', 'max:255'],
            'redirect_to' => ['nullable', 'string', 'max:2048'],
        ]);

        $redirectTo = $this->safeRedirectTo($validated['redirect_to'] ?? null);

        if (! $menu->is_available) {
            return redirect($redirectTo)->withErrors(['menu' => 'Menu sedang tidak tersedia.']);
        }

        RestaurantCart::addItem(
            $menu,
            (int) ($validated['qty'] ?? 1),
            trim((string) ($validated['notes'] ?? '')) ?: null,
        );

        return redirect($redirectTo)->with('success', $menu->name.' berhasil dimasukkan ke cart.');
    }

    private function safeRedirectTo(?string $redirectTo): string
    {
        if (! $redirectTo || ! str_starts_with($redirectTo, '/') || str_starts_with($redirectTo, '//')) {
            return url()->previous();
        }

        return $redirectTo;
    }
}
