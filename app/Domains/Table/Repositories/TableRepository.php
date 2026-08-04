<?php

namespace App\Domains\Table\Repositories;

use App\Domains\Table\Enums\TableStatus;
use App\Models\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TableRepository implements TableRepositoryInterface
{
    public function find(string $id): ?Table
    {
        return Table::query()->find($id);
    }

    public function byStatus(string $status): Collection
    {
        return Table::query()
            ->with('tableCategory')
            ->where('status', $status)
            ->orderBy('code')
            ->get();
    }

    public function countByStatus(string $status): int
    {
        return Table::query()->where('status', $status)->count();
    }

    public function allOrdered(): Collection
    {
        return Table::query()
            ->with('tableCategory')
            ->orderBy('code')
            ->get();
    }

    public function search(string $search): Collection
    {
        $search = trim($search);

        return Table::query()
            ->with('tableCategory')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('code', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('code')
            ->get();
    }

    public function selectable(): Collection
    {
        return $this->byStatus(TableStatus::Available->value);
    }

    public function paginateForAdmin(int $perPage = 12, string $search = ''): LengthAwarePaginator
    {
        $search = trim($search);

        return Table::query()
            ->with('tableCategory')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('code', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%')
                        ->orWhereHas('tableCategory', fn (Builder $category) => $category->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function update(Table $table, array $attributes): Table
    {
        $table->update($attributes);

        return $table;
    }

    public function closeActiveSessions(Table $table): void
    {
        $table->tableSessions()
            ->where('status', 'active')
            ->update(['status' => 'closed', 'closed_at' => now()]);
    }
}
