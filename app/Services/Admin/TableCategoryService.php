<?php

namespace App\Services\Admin;

use App\Models\TableCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TableCategoryService
{
    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator
    {
        $search = trim($search);

        return TableCategory::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(Request $request): TableCategory
    {
        $validated = $this->validate($request);

        return TableCategory::query()->create($validated);
    }

    public function update(Request $request, TableCategory $tableCategory): void
    {
        $validated = $this->validate($request, $tableCategory->id);

        $tableCategory->update($validated);
    }

    public function delete(TableCategory $tableCategory): void
    {
        if ($tableCategory->tables()->exists()) {
            throw ValidationException::withMessages([
                'table_category' => 'Kategori tidak bisa dihapus karena masih dipakai pada data meja.',
            ]);
        }

        $tableCategory->delete();
    }

    private function validate(Request $request, ?string $ignoreId = null): array
    {
        $request->merge([
            'slug' => Str::slug((string) ($request->input('slug') ?: $request->input('name'))),
        ]);

        $slugRule = Rule::unique('table_categories', 'slug');

        if ($ignoreId) {
            $slugRule = $slugRule->ignore($ignoreId);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:150', $slugRule],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
