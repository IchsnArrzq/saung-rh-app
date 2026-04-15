<?php

namespace App\Services\Admin;

use App\Models\TableStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TableStatusService
{
    /**
     * @var array<int, string>
     */
    private const RESERVED_KEYS = ['available', 'occupied', 'order_in', 'cleaning'];

    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator
    {
        $search = trim($search);

        return TableStatus::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('key', 'like', '%'.$search.'%')
                        ->orWhere('color', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(Request $request): TableStatus
    {
        $validated = $this->validate($request);

        $tableStatus = TableStatus::query()->create($validated);

        $this->syncDefaultFlag($tableStatus, $validated['is_default']);

        return $tableStatus;
    }

    public function update(Request $request, TableStatus $tableStatus): void
    {
        $validated = $this->validate($request, $tableStatus->id);

        if (in_array($tableStatus->key, self::RESERVED_KEYS, true) && $validated['key'] !== $tableStatus->key) {
            throw ValidationException::withMessages([
                'key' => 'Key untuk status sistem tidak boleh diubah.',
            ]);
        }

        $tableStatus->update($validated);

        $this->syncDefaultFlag($tableStatus, $validated['is_default']);
    }

    public function delete(TableStatus $tableStatus): void
    {
        if (in_array($tableStatus->key, self::RESERVED_KEYS, true)) {
            throw ValidationException::withMessages([
                'table_status' => 'Status sistem tidak dapat dihapus.',
            ]);
        }

        if ($tableStatus->tables()->exists()) {
            throw ValidationException::withMessages([
                'table_status' => 'Status tidak bisa dihapus karena masih dipakai pada data meja.',
            ]);
        }

        $wasDefault = (bool) $tableStatus->is_default;

        $tableStatus->delete();

        if (! $wasDefault) {
            return;
        }

        $nextDefault = TableStatus::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->first();

        if (! $nextDefault) {
            $nextDefault = TableStatus::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->first();
        }

        if ($nextDefault) {
            $nextDefault->update(['is_default' => true]);
        }
    }

    private function validate(Request $request, ?string $ignoreId = null): array
    {
        $request->merge([
            'key' => Str::of((string) $request->input('key'))
                ->trim()
                ->replace(['-', ' '], '_')
                ->lower()
                ->snake()
                ->toString(),
        ]);

        $keyRule = Rule::unique('table_statuses', 'key');

        if ($ignoreId) {
            $keyRule = $keyRule->ignore($ignoreId);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'key' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/', $keyRule],
            'color' => ['nullable', 'string', 'max:40'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_default'] = $request->boolean('is_default');

        return $validated;
    }

    private function syncDefaultFlag(TableStatus $tableStatus, bool $isDefault): void
    {
        if ($isDefault) {
            TableStatus::query()
                ->where('id', '!=', $tableStatus->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            if (! $tableStatus->is_default) {
                $tableStatus->update(['is_default' => true]);
            }

            return;
        }

        $hasDefault = TableStatus::query()->where('is_default', true)->exists();

        if (! $hasDefault) {
            $tableStatus->update(['is_default' => true]);
        }
    }
}
