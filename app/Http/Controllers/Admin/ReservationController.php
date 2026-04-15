<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\Admin\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(private readonly ReservationService $reservationService) {}

    public function index(): View
    {
        return view('admin.reservations.index', [
            'reservations' => $this->reservationService->paginate(),
        ]);
    }

    public function create(): View
    {
        return view('admin.reservations.create', [
            'tables' => $this->reservationService->tables(),
            'statusOptions' => $this->reservationService->statusOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->reservationService->create($request);

        return redirect()->route('reservations.index')->with('success', 'Reservasi berhasil ditambahkan.');
    }

    public function edit(Reservation $reservation): View
    {
        return view('admin.reservations.edit', [
            'reservation' => $reservation,
            'tables' => $this->reservationService->tables(),
            'statusOptions' => $this->reservationService->statusOptions(),
        ]);
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->reservationService->update($request, $reservation);

        return redirect()->route('reservations.index')->with('success', 'Reservasi berhasil diperbarui.');
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        $this->reservationService->delete($reservation);

        return redirect()->route('reservations.index')->with('success', 'Reservasi berhasil dihapus.');
    }
}
