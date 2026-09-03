<?php

namespace App\Domains\Reservation\UseCases;

use App\Domains\Menu\Repositories\MenuRepository;
use App\Domains\Reservation\DTO\PlaceReservationData;
use App\Domains\Reservation\Enums\ReservationStatus;
use App\Domains\Reservation\Repositories\ReservationRepository;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Books a table for later, with the customer's pre-ordered menu attached.
 *
 * Prices are snapshotted at booking time so a later menu price change cannot
 * silently rewrite what the guest agreed to.
 *
 * Replaces App\Services\Customer\BookingService::place — the last piece of the
 * Reservation domain that Fase C4 left behind because it lived under
 * `Services/Customer`.
 */
class PlaceReservationUseCase
{
    /** How close two bookings on one table may sit, in minutes. */
    private const HOLD_WINDOW_MINUTES = 90;

    public function __construct(
        private readonly ReservationRepository $reservations,
        private readonly MenuRepository $menus,
    ) {}

    public function handle(PlaceReservationData $data): Reservation
    {
        $reservationAt = Carbon::parse($data->reservationAt);

        if ($this->reservations->hasOverlappingHold($data->tableId, $reservationAt, self::HOLD_WINDOW_MINUTES)) {
            throw ValidationException::withMessages([
                'table_id' => 'Meja sudah terpakai di jam tersebut. Silakan pilih meja atau jam lain.',
            ]);
        }

        return DB::transaction(function () use ($data, $reservationAt): Reservation {
            return $this->reservations->createWithItems([
                'user_id' => $data->userId ?? Auth::id(),
                'table_id' => $data->tableId,
                'customer_name' => $data->customerName ?? Auth::user()?->name,
                'phone' => $data->phone,
                'pax' => $data->pax,
                'reservation_at' => $reservationAt,
                'status' => ReservationStatus::Pending->value,
                'notes' => $data->notes,
            ], $this->snapshotItems($data->items));
        });
    }

    /**
     * @param  array<int, array{menu_id:string,qty:int,notes?:?string}>  $items
     * @return array<int, array<string, mixed>>
     */
    private function snapshotItems(array $items): array
    {
        $menuMap = $this->menus->findManyKeyedById(array_column($items, 'menu_id'));
        $snapshot = [];

        foreach ($items as $item) {
            $menu = $menuMap->get($item['menu_id']);
            $qty = (int) $item['qty'];
            $price = (float) ($menu?->price ?? 0);

            $snapshot[] = [
                'menu_id' => $menu?->id,
                'menu_name_snapshot' => $menu?->name ?? 'Unknown Menu',
                'qty' => $qty,
                'unit_price' => $price,
                'line_total' => $qty * $price,
                'notes' => $item['notes'] ?? null,
            ];
        }

        return $snapshot;
    }
}
