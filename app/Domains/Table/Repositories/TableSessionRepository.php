<?php

namespace App\Domains\Table\Repositories;

use App\Domains\Table\Enums\TableSessionCloseReason;
use App\Domains\Table\Enums\TableSessionStatus;
use App\Models\TableSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TableSessionRepository
{
    public function find(string $id): ?TableSession
    {
        return TableSession::query()->with('table')->find($id);
    }

    /** Locked until the surrounding transaction ends — two staff cannot decide one scan twice. */
    public function findForUpdate(string $id): ?TableSession
    {
        return TableSession::query()->lockForUpdate()->find($id);
    }

    /**
     * @param  array<int, string>  $statuses
     */
    public function findInStatus(string $id, array $statuses): ?TableSession
    {
        return TableSession::query()
            ->with('table')
            ->whereIn('status', $statuses)
            ->find($id);
    }

    /** The session currently holding a table — waiting or seated — if any. */
    public function liveForTable(string $tableId): ?TableSession
    {
        return TableSession::query()
            ->where('table_id', $tableId)
            ->whereIn('status', TableSessionStatus::liveValues())
            ->latest('started_at')
            ->first();
    }

    /** Guests waiting for staff, oldest first so nobody gets skipped. */
    public function pending(): Collection
    {
        return TableSession::query()
            ->with('table')
            ->where('status', TableSessionStatus::Pending->value)
            ->oldest('started_at')
            ->get();
    }

    public function paginateForAdmin(string $search = '', ?TableSessionStatus $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $search = trim($search);

        return TableSession::query()
            ->with(['table', 'approver', 'closer'])
            ->withCount('orders')
            ->when($status, fn (Builder $query) => $query->where('status', $status->value))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->whereLike('customer_name', '%'.$search.'%')
                        ->orWhereHas('table', fn (Builder $table) => $table->whereLike('code', '%'.$search.'%'));
                });
            })
            ->latest('started_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): TableSession
    {
        return TableSession::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(TableSession $session, array $attributes): TableSession
    {
        $session->update($attributes);

        return $session;
    }

    /** Ends whatever session still holds a table, waiting or seated. */
    public function closeLiveForTable(string $tableId, TableSessionCloseReason $reason): int
    {
        return TableSession::query()
            ->where('table_id', $tableId)
            ->whereIn('status', TableSessionStatus::liveValues())
            ->update([
                'status' => TableSessionStatus::Closed->value,
                'close_reason' => $reason->value,
                'closed_at' => now(),
            ]);
    }
}
