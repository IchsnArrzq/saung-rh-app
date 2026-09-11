<?php

namespace App\Domains\Table\Repositories;

use App\Domains\Table\Enums\TableStatus;
use App\Models\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class TableRepository
{
    public function find(string $id): ?Table
    {
        return Table::query()->find($id);
    }

    /** The table behind a printed QR code. */
    public function findByQrToken(string $token): ?Table
    {
        return Table::query()->where('qr_token', $token)->first();
    }

    /** Locked until the surrounding transaction ends. */
    public function findForUpdate(string $id): ?Table
    {
        return Table::query()->lockForUpdate()->find($id);
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
     * Every table with its current QR session — the staff Panel meja.
     * `tableSessions` holds only active sessions, newest first (normally one).
     */
    public function allWithActiveSession(): Collection
    {
        return Table::query()
            ->with(['tableCategory', 'tableSessions' => fn ($query) => $query->active()->latest('started_at')])
            ->orderBy('code')
            ->get();
    }

    /** One table with its current QR session; null for an unknown or malformed id. */
    public function findWithActiveSession(string $id): ?Table
    {
        if (! Str::isUuid($id)) {
            return null;
        }

        return Table::query()
            ->with(['tableCategory', 'tableSessions' => fn ($query) => $query->active()->latest('started_at')])
            ->find($id);
    }

    /**
     * Tables in any of the given statuses except one — e.g. the occupied tables a
     * guest can open a table-to-table chat with.
     *
     * @param  array<int, string>  $statuses
     */
    public function inStatusesExcept(array $statuses, string $exceptId): Collection
    {
        return Table::query()
            ->whereIn('status', $statuses)
            ->whereKeyNot($exceptId)
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
                    $inner->whereLike('code', '%'.$search.'%')
                        ->orWhereLike('name', '%'.$search.'%')
                        ->orWhereLike('capacity', '%'.$search.'%')
                        ->orWhereLike('status', '%'.$search.'%')
                        ->orWhereHas('tableCategory', fn (Builder $category) => $category->whereLike('name', '%'.$search.'%'));
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
                    $inner->whereLike('code', '%'.$search.'%')
                        ->orWhereLike('name', '%'.$search.'%')
                        ->orWhereLike('status', '%'.$search.'%')
                        ->orWhereHas('tableCategory', fn (Builder $category) => $category->whereLike('name', '%'.$search.'%'));
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
}
