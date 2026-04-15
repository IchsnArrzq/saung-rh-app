<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IngredientController extends Controller
{
    public function index(): View
    {
        $ingredients = Ingredient::query()->latest()->paginate(12);

        return view('admin.ingredients.index', compact('ingredients'));
    }

    public function create(): View
    {
        return view('admin.ingredients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateIngredient($request);
        $validated['is_active'] = $request->boolean('is_active');

        Ingredient::create($validated);

        return redirect()->route('ingredients.index')->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    public function edit(Ingredient $ingredient): View
    {
        return view('admin.ingredients.edit', compact('ingredient'));
    }

    public function update(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $validated = $this->validateIngredient($request);
        $validated['is_active'] = $request->boolean('is_active');

        $ingredient->update($validated);

        return redirect()->route('ingredients.index')->with('success', 'Bahan baku berhasil diperbarui.');
    }

    public function destroy(Ingredient $ingredient): RedirectResponse
    {
        $ingredient->delete();

        return redirect()->route('ingredients.index')->with('success', 'Bahan baku berhasil dihapus.');
    }

    private function validateIngredient(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'unit' => ['required', 'string', 'max:20'],
            'current_stock' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
