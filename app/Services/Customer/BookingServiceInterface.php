<?php

namespace App\Services\Customer;

use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Database\Eloquent\Collection;

interface BookingServiceInterface
{
    /**
     * @return array{tables: Collection<int, Table>, menus: Collection<int, Menu>}
     */
    public function createFormData(): array;

    /**
     * Create a future table reservation with pre-ordered items.
     *
     * @param  array{table_id:string,pax:int,reservation_at:string,notes:?string,items:array<int,array{menu_id:string,qty:int,notes:?string}>}  $validated
     */
    public function place(array $validated): Reservation;
}
