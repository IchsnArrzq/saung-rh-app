<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Admin\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    public function index(): View
    {
        return view('admin.payments.index', [
            'payments' => $this->paymentService->paginate(),
        ]);
    }

    public function create(): View
    {
        return view('admin.payments.create', [
            'orders' => $this->paymentService->orders(),
            'methodOptions' => $this->paymentService->methodOptions(),
            'statusOptions' => $this->paymentService->statusOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->paymentService->create($request);

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil ditambahkan.');
    }

    public function edit(Payment $payment): View
    {
        return view('admin.payments.edit', [
            'payment' => $payment,
            'orders' => $this->paymentService->orders(),
            'methodOptions' => $this->paymentService->methodOptions(),
            'statusOptions' => $this->paymentService->statusOptions(),
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $this->paymentService->update($request, $payment);

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $this->paymentService->delete($payment);

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil dihapus.');
    }
}
