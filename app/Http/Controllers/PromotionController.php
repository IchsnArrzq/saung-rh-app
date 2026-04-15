<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PromotionController extends Controller
{
    private const TYPE_OPTIONS = ['percent', 'amount', 'bogo'];

    public function index(): View
    {
        $promotions = Promotion::query()->latest()->paginate(12);

        return view('admin.promotions.index', compact('promotions'));
    }

    public function create(): View
    {
        return view('admin.promotions.create', [
            'typeOptions' => self::TYPE_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePromotion($request);
        $validated['is_active'] = $request->boolean('is_active');

        Promotion::create($validated);

        return redirect()->route('promotions.index')->with('success', 'Promo berhasil ditambahkan.');
    }

    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.edit', [
            'promotion' => $promotion,
            'typeOptions' => self::TYPE_OPTIONS,
        ]);
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $validated = $this->validatePromotion($request);
        $validated['is_active'] = $request->boolean('is_active');

        $promotion->update($validated);

        return redirect()->route('promotions.index')->with('success', 'Promo berhasil diperbarui.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->delete();

        return redirect()->route('promotions.index')->with('success', 'Promo berhasil dihapus.');
    }

    private function validatePromotion(Request $request): array
    {
        $promotion = $request->route('promotion');
        $ignoreId = $promotion instanceof Promotion ? $promotion->id : $promotion;

        return $request->validate([
            'code' => ['required', 'string', 'max:40', Rule::unique('promotions', 'code')->ignore($ignoreId)],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in(self::TYPE_OPTIONS)],
            'value' => ['nullable', 'numeric', 'min:0'],
            'min_purchase' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
