<?php

namespace App\Domains\Reservation\Repositories;

use App\Models\Reservation;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

interface ReservationRepositoryInterface
{
    public function find(string $id): ?Reservation;

    public function findWithTable(string $id): ?Reservation;

    /** Admin listing, newest first. */
    public function paginateForAdmin(int $perPage = 12, string $search = ''): LengthAwarePaginator;

    /**
     * Receptionist board: same search, but ordered by urgency
     * (pending, then confirmed, then the rest) and filterable by status.
     */
    public function paginateForBoard(int $perPage = 12, string $search = '', string $statusFilter = 'all'): LengthAwarePaginator;

    /** status => count, for the board's summary chips. */
    public function countsByStatus(): Collection;

    public function countForDate(string $date): int;

    /**
     * A day's bookings in arrival order — the dashboard's "today" panel.
     *
     * @return EloquentCollection<int, Reservation>
     */
    public function listForDate(CarbonInterface $date, int $limit = 6): EloquentCollection;

    /**
     * A customer's still-open bookings, soonest first.
     *
     * @return EloquentCollection<int, Reservation>
     */
    public function upcomingForUser(string $userId, int $limit = 5): EloquentCollection;

    /**
     * A customer's booking history, newest first.
     *
     * @return EloquentCollection<int, Reservation>
     */
    public function historyForUser(string $userId, int $limit = 10): EloquentCollection;

    /**
     * Whether the table is already held by another booking within `$windowMinutes`
     * either side of the requested time — the double-booking check.
     */
    public function hasOverlappingHold(string $tableId, CarbonInterface $at, int $windowMinutes = 90): bool;

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, array<string, mixed>>  $items
     */
    public function createWithItems(array $attributes, array $items): Reservation;

    /**
     * Pending bookings whose deposit window lapsed unpaid, table eager-loaded.
     *
     * @return EloquentCollection<int, Reservation>
     */
    public function expiredHolds(): EloquentCollection;

    /**
     * Confirmed bookings past their grace window with nobody checked in.
     *
     * @return EloquentCollection<int, Reservation>
     */
    public function noShowCandidates(int $graceMinutes): EloquentCollection;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Reservation;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Reservation $reservation, array $attributes): Reservation;

    public function delete(Reservation $reservation): void;
}
