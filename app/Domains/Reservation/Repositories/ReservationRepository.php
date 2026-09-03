<?php

namespace App\Domains\Reservation\Repositories;

use App\Domains\Reservation\Enums\ReservationStatus;
use App\Models\Reservation;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class ReservationRepository
{
    public function find(string $id): ?Reservation
    {
        return Reservation::query()->find($id);
    }

    public function findWithTable(string $id): ?Reservation
    {
        return Reservation::query()->with('table')->find($id);
    }

    /** Admin listing, newest first. */
    public function paginateForAdmin(int $perPage = 12, string $search = ''): LengthAwarePaginator
    {
        return Reservation::query()
            ->with('table')
            ->withCount('items')
            ->tap(fn (Builder $query) => $this->applySearch($query, $search, withStatus: true))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Receptionist board: same search, but ordered by urgency
     * (pending, then confirmed, then the rest) and filterable by status.
     */
    public function paginateForBoard(int $perPage = 12, string $search = '', string $statusFilter = 'all'): LengthAwarePaginator
    {
        return Reservation::query()
            ->with('table')
            ->withCount('items')
            ->tap(fn (Builder $query) => $this->applySearch($query, $search))
            ->when($statusFilter !== 'all', fn (Builder $query) => $query->where('status', $statusFilter))
            ->orderByRaw(
                'CASE WHEN status = ? THEN 0 WHEN status = ? THEN 1 ELSE 2 END',
                [ReservationStatus::Pending->value, ReservationStatus::Confirmed->value],
            )
            ->orderBy('reservation_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** status => count, for the board's summary chips. */
    public function countsByStatus(): Collection
    {
        return Reservation::query()
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');
    }

    public function countForDate(string $date): int
    {
        return Reservation::query()->whereDate('reservation_at', $date)->count();
    }

    /**
     * A day's bookings in arrival order — the dashboard's "today" panel.
     *
     * @return EloquentCollection<int, Reservation>
     */
    public function listForDate(CarbonInterface $date, int $limit = 6): EloquentCollection
    {
        return Reservation::query()
            ->with('table:id,code,name')
            ->whereDate('reservation_at', $date)
            ->orderBy('reservation_at')
            ->limit($limit)
            ->get();
    }

    /**
     * A customer's still-open bookings, soonest first.
     *
     * @return EloquentCollection<int, Reservation>
     */
    public function upcomingForUser(string $userId, int $limit = 5): EloquentCollection
    {
        return Reservation::query()
            ->with(['table', 'items'])
            ->where('user_id', $userId)
            ->whereIn('status', [ReservationStatus::Pending->value, ReservationStatus::Confirmed->value])
            ->where('reservation_at', '>=', now())
            ->orderBy('reservation_at')
            ->limit($limit)
            ->get();
    }

    /**
     * A customer's booking history, newest first.
     *
     * @return EloquentCollection<int, Reservation>
     */
    public function historyForUser(string $userId, int $limit = 10): EloquentCollection
    {
        return Reservation::query()
            ->with(['table', 'items'])
            ->where('user_id', $userId)
            ->latest('reservation_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Whether the table is already held by another booking within `$windowMinutes`
     * either side of the requested time — the double-booking check.
     */
    public function hasOverlappingHold(string $tableId, CarbonInterface $at, int $windowMinutes = 90): bool
    {
        return Reservation::query()
            ->where('table_id', $tableId)
            ->whereIn('status', [
                ReservationStatus::Pending->value,
                ...ReservationStatus::holdingValues(),
            ])
            ->whereBetween('reservation_at', [
                $at->copy()->subMinutes($windowMinutes),
                $at->copy()->addMinutes($windowMinutes),
            ])
            ->exists();
    }

    /**
     * Pending bookings whose deposit window lapsed unpaid, table eager-loaded.
     *
     * @return EloquentCollection<int, Reservation>
     */
    public function expiredHolds(): EloquentCollection
    {
        return Reservation::query()->expiredHolds()->with('table')->get();
    }

    /**
     * Confirmed bookings past their grace window with nobody checked in.
     *
     * @return EloquentCollection<int, Reservation>
     */
    public function noShowCandidates(int $graceMinutes): EloquentCollection
    {
        return Reservation::query()->noShowCandidates($graceMinutes)->with('table')->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Reservation
    {
        return Reservation::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, array<string, mixed>>  $items
     */
    public function createWithItems(array $attributes, array $items): Reservation
    {
        $reservation = Reservation::query()->create($attributes);
        $reservation->items()->createMany($items);

        return $reservation;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Reservation $reservation, array $attributes): Reservation
    {
        $reservation->update($attributes);

        return $reservation;
    }

    public function delete(Reservation $reservation): void
    {
        $reservation->delete();
    }

    private function applySearch(Builder $query, string $search, bool $withStatus = false): void
    {
        $search = trim($search);

        if ($search === '') {
            return;
        }

        $query->where(function (Builder $inner) use ($search, $withStatus): void {
            $inner->where('customer_name', 'like', '%'.$search.'%')
                ->orWhere('phone', 'like', '%'.$search.'%')
                ->orWhereHas('table', fn (Builder $table) => $table->where('code', 'like', '%'.$search.'%'));

            if ($withStatus) {
                $inner->orWhere('status', 'like', '%'.$search.'%');
            }
        });
    }
}
