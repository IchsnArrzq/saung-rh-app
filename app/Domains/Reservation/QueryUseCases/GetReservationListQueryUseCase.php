<?php

namespace App\Domains\Reservation\QueryUseCases;

use App\Domains\Reservation\Repositories\ReservationRepository;
use App\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class GetReservationListQueryUseCase
{
    public function __construct(private readonly ReservationRepository $reservations) {}

    /** Admin table. */
    public function forAdmin(string $search = '', int $perPage = 12): LengthAwarePaginator
    {
        return $this->reservations->paginateForAdmin($perPage, $search);
    }

    /** Satu baris reservasi — cukup untuk diadili Policy sebelum aksi jalan. */
    public function find(string $id): ?Reservation
    {
        return $this->reservations->find($id);
    }

    /** Receptionist board, ordered by urgency. */
    public function forBoard(string $search = '', string $statusFilter = 'all', int $perPage = 12): LengthAwarePaginator
    {
        return $this->reservations->paginateForBoard($perPage, $search, $statusFilter);
    }

    public function countsByStatus(): Collection
    {
        return $this->reservations->countsByStatus();
    }

    public function countToday(): int
    {
        return $this->reservations->countForDate((string) today()->toDateString());
    }
}
