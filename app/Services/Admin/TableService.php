<?php

namespace App\Services\Admin;

use App\Models\TableCategory;
use App\Models\TableStatus;
use App\Models\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TableService
{
    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator
    {
        $search = trim($search);

        return Table::query()
            ->with(['tableStatus', 'tableCategory'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('code', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('capacity', 'like', '%'.$search.'%')
                        ->orWhereHas('tableStatus', fn ($status) => $status->where(function ($statusQuery) use ($search) {
                            $statusQuery->where('name', 'like', '%'.$search.'%')
                                ->orWhere('key', 'like', '%'.$search.'%');
                        }))
                        ->orWhereHas('tableCategory', fn ($category) => $category->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, TableStatus>
     */
    public function statusOptions(?Table $table = null): Collection
    {
        return TableStatus::query()
            ->where(function ($query) use ($table) {
                $query->where('is_active', true);

                if ($table?->table_status_id) {
                    $query->orWhere('id', $table->table_status_id);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, TableStatus>
     */
    public function boardStatuses(): Collection
    {
        return TableStatus::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, TableCategory>
     */
    public function categoryOptions(?Table $table = null): Collection
    {
        return TableCategory::query()
            ->where(function ($query) use ($table) {
                $query->where('is_active', true);

                if ($table?->table_category_id) {
                    $query->orWhere('id', $table->table_category_id);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function create(Request $request): Table
    {
        $validated = $this->validate($request);

        return Table::query()->create($validated);
    }

    public function update(Request $request, Table $table): void
    {
        $validated = $this->validate($request, $table->id);

        $table->update($validated);
    }

    public function delete(Table $table): void
    {
        $table->delete();
    }

    public function updateStatus(Table $table, string $statusId): void
    {
        $status = TableStatus::query()->find($statusId);

        if (! $status) {
            throw ValidationException::withMessages([
                'table_status_id' => 'Status meja tidak valid.',
            ]);
        }

        $table->update([
            'table_status_id' => $status->id,
            'status' => $status->key,
        ]);
    }

    private function validate(Request $request, ?string $ignoreTableId = null): array
    {
        $codeRule = Rule::unique('tables', 'code');

        if ($ignoreTableId) {
            $codeRule = $codeRule->ignore($ignoreTableId);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', $codeRule],
            'name' => ['nullable', 'string', 'max:120'],
            'capacity' => ['required', 'integer', 'min:1'],
            'table_status_id' => ['required', 'exists:table_statuses,id'],
            'table_category_id' => ['nullable', 'exists:table_categories,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $status = TableStatus::query()->find($validated['table_status_id']);

        if (! $status) {
            throw ValidationException::withMessages([
                'table_status_id' => 'Status meja tidak valid.',
            ]);
        }

        $validated['status'] = $status->key;

        return $validated;
    }
}
