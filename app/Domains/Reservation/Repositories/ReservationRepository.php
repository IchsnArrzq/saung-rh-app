<?php

namespace App\Domains\Reservation\Repositories;

use App\Domains\Reservation\Enums\ReservationStatus;
use App\Models\Reservation;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class ReservationRepository implements ReservationRepositoryInterface
{
    public function find(string $id): ?Reservation
    {
        return Reservation::query()->find($id);
    }

    public function findWithTable(string $id): ?Reservation
    {
        return Reservation::query()->with('table')->find($id);
    }

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

    public function listForDate(CarbonInterface $date, int $limit = 6): EloquentCollection
    {
        return Reservation::query()
            ->with('table:id,code,name')
            ->whereDate('reservation_at', $date)
            ->orderBy('reservation_at')
            ->limit($limit)
            ->get();
    }

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

    public function historyForUser(string $userId, int $limit = 10): EloquentCollection
    {
        return Reservation::query()
            ->with(['table', 'items'])
            ->where('user_id', $userId)
            ->latest('reservation_at')
            ->limit($limit)
            ->get();
    }

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

    public function expiredHolds(): EloquentCollection
    {
        return Reservation::query()->expiredHolds()->with('table')->get();
    }

    public function noShowCandidates(int $graceMinutes): EloquentCollection
    {
        return Reservation::query()->noShowCandidates($graceMinutes)->with('table')->get();
    }

    public function create(array $attributes): Reservation
    {
        return Reservation::query()->create($attributes);
    }

    public function createWithItems(array $attributes, array $items): Reservation
    {
        $reservation = Reservation::query()->create($attributes);
        $reservation->items()->createMany($items);

        return $reservation;
    }

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
