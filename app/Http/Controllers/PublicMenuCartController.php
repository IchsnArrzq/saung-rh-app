<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Services\Landing\PublicCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublicMenuCartController extends Controller
{
    public function __construct(private readonly PublicCartService $publicCartService) {}

    public function store(Request $request, Menu $menu): RedirectResponse
    {
        $validated = $request->validate([
            'qty' => ['nullable', 'integer', 'min:1', 'max:20'],
            'notes' => ['nullable', 'string', 'max:255'],
            'redirect_to' => ['nullable', 'string', 'max:2048'],
        ]);

        try {
            $this->publicCartService->quickAdd(
                $menu,
                (int) ($validated['qty'] ?? 1),
                trim((string) ($validated['notes'] ?? '')) ?: null,
            );
        } catch (ValidationException $exception) {
            return redirect($this->safeRedirectTo($validated['redirect_to'] ?? null))
                ->withErrors($exception->errors());
        }

        return redirect($this->safeRedirectTo($validated['redirect_to'] ?? null))
            ->with('success', $menu->name.' berhasil dimasukkan ke cart.');
    }

    private function safeRedirectTo(?string $redirectTo): string
    {
        if (! $redirectTo || ! str_starts_with($redirectTo, '/') || str_starts_with($redirectTo, '//')) {
            return url()->previous();
        }

        return $redirectTo;
    }
}
