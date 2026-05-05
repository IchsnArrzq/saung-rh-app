<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Payment::class);

        return view('admin.payments.index');
    }

    public function create(): View
    {
        $this->authorize('create', Payment::class);

        return view('admin.payments.create');
    }

    public function edit(Payment $payment): View
    {
        $this->authorize('update', $payment);

        return view('admin.payments.edit', [
            'payment' => $payment,
        ]);
    }
}
