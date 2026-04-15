<?php

namespace App\Services\Admin;

use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReservationService
{
    public const STATUS_OPTIONS = ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'];

    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator
    {
        $search = trim($search);

        return Reservation::query()
            ->with('table')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%')
                        ->orWhereHas('table', fn ($table) => $table->where('code', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function tables(): Collection
    {
        return Table::query()->orderBy('code')->get();
    }

    /**
     * @return array<int, string>
     */
    public function statusOptions(): array
    {
        return self::STATUS_OPTIONS;
    }

    public function create(Request $request): void
    {
        $validated = $this->validate($request);

        Reservation::query()->create($validated);
    }

    public function update(Request $request, Reservation $reservation): void
    {
        $validated = $this->validate($request);

        $reservation->update($validated);
    }

    public function delete(Reservation $reservation): void
    {
        $reservation->delete();
    }

    private function validate(Request $request): array
    {
        return $request->validate([
            'table_id' => ['nullable', 'exists:tables,id'],
            'customer_name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'pax' => ['required', 'integer', 'min:1'],
            'reservation_at' => ['required', 'date'],
            'status' => ['required', Rule::in(self::STATUS_OPTIONS)],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
