<?php

namespace App\Services\Admin;

use App\Models\MenuCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MenuCategoryService
{
    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator
    {
        $search = trim($search);

        return MenuCategory::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(Request $request): MenuCategory
    {
        $validated = $this->validate($request);

        return MenuCategory::query()->create($validated);
    }

    public function update(Request $request, MenuCategory $menuCategory): void
    {
        $validated = $this->validate($request, $menuCategory->id);

        $menuCategory->update($validated);
    }

    public function delete(MenuCategory $menuCategory): void
    {
        $menuCategory->delete();
    }

    private function validate(Request $request, ?string $ignoreCategoryId = null): array
    {
        $slugRule = Rule::unique('menu_categories', 'slug');

        if ($ignoreCategoryId) {
            $slugRule = $slugRule->ignore($ignoreCategoryId);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150', $slugRule],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
