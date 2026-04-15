<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerBookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService) {}

    public function create(): View
    {
        return view('customer.bookings.create', $this->bookingService->createFormData());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->bookingService->createReservation($request);

        return redirect()
            ->route('customer.dashboard')
            ->with('success', 'Reservasi berhasil dibuat. Tim kami akan segera mengonfirmasi booking Anda.');
    }
}
