<?php

namespace App\Services\Admin;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MenuService
{
    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator
    {
        $search = trim($search);

        return Menu::query()
            ->with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function categories(?Menu $menu = null): Collection
    {
        return MenuCategory::query()
            ->where('is_active', true)
            ->when($menu?->menu_category_id, fn ($query) => $query->orWhere('id', $menu->menu_category_id))
            ->orderBy('name')
            ->get();
    }

    public function create(Request $request): Menu
    {
        $validated = $this->validate($request);

        return Menu::query()->create($validated);
    }

    public function update(Request $request, Menu $menu): void
    {
        $validated = $this->validate($request, $menu->id);

        $menu->update($validated);
    }

    public function delete(Menu $menu): void
    {
        $menu->delete();
    }

    private function validate(Request $request, ?string $ignoreMenuId = null): array
    {
        $slugRule = Rule::unique('menus', 'slug');
        $skuRule = Rule::unique('menus', 'sku');

        if ($ignoreMenuId) {
            $slugRule = $slugRule->ignore($ignoreMenuId);
            $skuRule = $skuRule->ignore($ignoreMenuId);
        }

        $validated = $request->validate([
            'menu_category_id' => ['nullable', 'exists:menu_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', $slugRule],
            'sku' => ['nullable', 'string', 'max:60', $skuRule],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        unset($validated['is_available']);
        $validated['menu_status_id'] = $this->resolveMenuStatusId($request->boolean('is_available'));

        return $validated;
    }

    private function resolveMenuStatusId(bool $available): ?string
    {
        $key = $available ? 'available' : 'unavailable';

        return MenuStatus::query()->where('key', $key)->value('id')
            ?? MenuStatus::query()->where('key', 'available')->value('id');
    }
}
