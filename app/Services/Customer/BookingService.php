<?php

namespace App\Services\Customer;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Reservation;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
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
            'menus' => Menu::query()
                ->with('category:id,name')
                ->available()
                ->orderBy('name')
                ->get(),
            'categories' => MenuCategory::query()
                ->where('is_active', true)
                ->withCount(['menus' => fn ($q) => $q->available()])
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * Create a future table reservation with pre-ordered items.
     *
     * @param  array{table_id:string,pax:int,reservation_at:string,notes:?string,items:array<int,array{menu_id:string,qty:int,notes:?string}>}  $validated
     */
    public function place(array $validated): Reservation
    {
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

        return DB::transaction(function () use ($validated): Reservation {
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

            return $reservation;
        });
    }
}
