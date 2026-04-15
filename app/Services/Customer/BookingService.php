<?php

namespace App\Services\Customer;

use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    private const STATUS_PENDING = 'pending';

    /**
     * @return array{tables: Collection<int, Table>, menus: Collection<int, Menu>}
     */
    public function createFormData(): array
    {
        return [
            'tables' => Table::query()
                ->with('tableStatus')
                ->whereHas('tableStatus', fn ($query) => $query->where('key', 'available'))
                ->orderBy('code')
                ->get(),
            'menus' => Menu::query()->where('is_available', true)->orderBy('name')->get(),
        ];
    }

    public function createReservation(Request $request): void
    {
        $validated = $request->validate([
            'table_id' => ['required', 'exists:tables,id'],
            'pax' => ['required', 'integer', 'min:1', 'max:30'],
            'reservation_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'exists:menus,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:20'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $reservationAt = Carbon::parse($validated['reservation_at']);

        $tableAlreadyBooked = Reservation::query()
            ->where('table_id', $validated['table_id'])
            ->whereIn('status', ['pending', 'confirmed', 'seated'])
            ->whereBetween('reservation_at', [
                $reservationAt->copy()->subMinutes(90),
                $reservationAt->copy()->addMinutes(90),
            ])
            ->exists();

        if ($tableAlreadyBooked) {
            throw ValidationException::withMessages([
                'table_id' => 'Meja sudah terpakai di jam tersebut. Silakan pilih meja atau jam lain.',
            ]);
        }

        DB::transaction(function () use ($validated): void {
            $menuMap = Menu::query()
                ->whereIn('id', collect($validated['items'])->pluck('menu_id'))
                ->get()
                ->keyBy('id');

            $reservation = Reservation::query()->create([
                'user_id' => Auth::id(),
                'table_id' => $validated['table_id'],
                'customer_name' => Auth::user()?->name,
                'phone' => null,
                'pax' => $validated['pax'],
                'reservation_at' => $validated['reservation_at'],
                'status' => self::STATUS_PENDING,
                'notes' => $validated['notes'] ?? null,
            ]);

            $items = collect($validated['items'])
                ->map(function (array $item) use ($menuMap): array {
                    $menu = $menuMap->get($item['menu_id']);
                    $qty = (int) $item['qty'];
                    $price = (float) ($menu?->price ?? 0);

                    return [
                        'menu_id' => $menu?->id,
                        'menu_name_snapshot' => $menu?->name ?? 'Unknown Menu',
                        'qty' => $qty,
                        'unit_price' => $price,
                        'line_total' => $qty * $price,
                        'notes' => $item['notes'] ?? null,
                    ];
                })
                ->values()
                ->all();

            $reservation->items()->createMany($items);
        });
    }
}
