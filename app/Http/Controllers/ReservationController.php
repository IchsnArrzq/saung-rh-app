<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReservationController extends Controller
{
    private const STATUS_OPTIONS = ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'];

    public function index(): View
    {
        $reservations = Reservation::query()->with('table')->latest()->paginate(12);

        return view('admin.reservations.index', compact('reservations'));
    }

    public function create(): View
    {
        return view('admin.reservations.create', [
            'tables' => Table::query()->orderBy('code')->get(),
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateReservation($request);

        Reservation::create($validated);

        return redirect()->route('reservations.index')->with('success', 'Reservasi berhasil ditambahkan.');
    }

    public function edit(Reservation $reservation): View
    {
        return view('admin.reservations.edit', [
            'reservation' => $reservation,
            'tables' => Table::query()->orderBy('code')->get(),
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        $validated = $this->validateReservation($request);

        $reservation->update($validated);

        return redirect()->route('reservations.index')->with('success', 'Reservasi berhasil diperbarui.');
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        $reservation->delete();

        return redirect()->route('reservations.index')->with('success', 'Reservasi berhasil dihapus.');
    }

    private function validateReservation(Request $request): array
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
