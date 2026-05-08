<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Reservation::class);

        return view('admin.reservations.index');
    }

    public function create(): View
    {
        $this->authorize('create', Reservation::class);

        return view('admin.reservations.create');
    }

    public function edit(Reservation $reservation): View
    {
        $this->authorize('update', $reservation);

        return view('admin.reservations.edit', [
            'reservation' => $reservation,
        ]);
    }
}
