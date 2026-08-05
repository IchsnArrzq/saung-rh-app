<?php

namespace App\Domains\Reservation\Repositories;

use App\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Reservation;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Reservation $reservation, array $attributes): Reservation;

    public function delete(Reservation $reservation): void;
}
