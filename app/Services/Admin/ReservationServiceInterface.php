<?php

namespace App\Services\Admin;

use App\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface ReservationServiceInterface
{
    public const STATUS_OPTIONS = ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'];

    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator;

    public function tables(): Collection;

    /**
     * @return array<int, string>
     */
    public function statusOptions(): array;

    public function create(Request $request): void;

    public function update(Request $request, Reservation $reservation): void;

    public function delete(Reservation $reservation): void;
}
