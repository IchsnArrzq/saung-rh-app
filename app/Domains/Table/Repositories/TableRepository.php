<?php

namespace App\Domains\Table\Repositories;

use App\Domains\Table\Enums\TableStatus;
use App\Models\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TableRepository
{
    public function find(string $id): ?Table
    {
        return Table::query()->find($id);
    }

    /** Tables in a given status, ordered by code. */
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

    /** Every table for the floor board / pickers, category eager-loaded. */
    public function allOrdered(): Collection
    {
        return Table::query()
            ->with('tableCategory')
            ->orderBy('code')
            ->get();
    }

    /**
     * Full (unpaginated) list filtered by code, name, capacity, status or
     * category — floor tools show every table and never paginate.
     */
    public function search(string $search): Collection
    {
        $search = trim($search);

        return Table::query()
            ->with('tableCategory')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('code', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('capacity', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%')
                        ->orWhereHas('tableCategory', fn (Builder $category) => $category->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('code')
            ->get();
    }

    /** Free tables only — what a customer may pick or book. */
    public function selectable(): Collection
    {
        return $this->byStatus(TableStatus::Available->value);
    }

    /** Tables a guest may still send an order to — free, seated, or already mid-order. */
    public function orderable(): Collection
    {
        return Table::query()
            ->with('tableCategory')
            ->whereIn('status', TableStatus::orderableValues())
            ->orderBy('code')
            ->get();
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

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Table $table, array $attributes): Table
    {
        $table->update($attributes);

        return $table;
    }

    /** Ends every active QR session on a table. */
    public function closeActiveSessions(Table $table): void
    {
        $table->tableSessions()
            ->where('status', 'active')
            ->update(['status' => 'closed', 'closed_at' => now()]);
    }
}
