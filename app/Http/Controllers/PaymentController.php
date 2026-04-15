<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    private const METHOD_OPTIONS = ['cash', 'qris', 'debit_card', 'credit_card', 'transfer', 'ewallet'];

    private const TYPE_OPTIONS = ['full', 'dp', 'partial'];

    private const STATUS_OPTIONS = ['pending', 'paid', 'failed', 'refunded'];

    public function index(): View
    {
        $payments = Payment::query()->with('order')->latest()->paginate(12);

        return view('admin.payments.index', compact('payments'));
    }

    public function create(): View
    {
        return view('admin.payments.create', [
            'orders' => Order::query()->orderByDesc('created_at')->get(),
            'methodOptions' => self::METHOD_OPTIONS,
            'typeOptions' => self::TYPE_OPTIONS,
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayment($request);

        $validated['paid_at'] = $validated['paid_at'] ?? ($validated['status'] === 'paid' ? now() : null);

        Payment::create($validated);

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil ditambahkan.');
    }

    public function edit(Payment $payment): View
    {
        return view('admin.payments.edit', [
            'payment' => $payment,
            'orders' => Order::query()->orderByDesc('created_at')->get(),
            'methodOptions' => self::METHOD_OPTIONS,
            'typeOptions' => self::TYPE_OPTIONS,
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $this->validatePayment($request);
        $validated['paid_at'] = $validated['paid_at'] ?? ($validated['status'] === 'paid' ? now() : null);

        $payment->update($validated);

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $payment->delete();

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil dihapus.');
    }

    private function validatePayment(Request $request): array
    {
        return $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'method' => ['required', Rule::in(self::METHOD_OPTIONS)],
            'type' => ['required', Rule::in(self::TYPE_OPTIONS)],
            'status' => ['required', Rule::in(self::STATUS_OPTIONS)],
            'amount' => ['required', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'paid_at' => ['nullable', 'date'],
        ]);
    }
}
