<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentService
{
    public const METHOD_OPTIONS = ['cash', 'qris', 'debit_card', 'credit_card', 'transfer', 'ewallet'];

    public const STATUS_OPTIONS = ['pending', 'paid', 'failed', 'refunded'];

    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator
    {
        $search = trim($search);

        return Payment::query()
            ->with('order')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('method', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%')
                        ->orWhere('reference', 'like', '%'.$search.'%')
                        ->orWhereHas('order', fn ($order) => $order->where('order_number', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function orders(): Collection
    {
        return Order::query()->orderByDesc('created_at')->get();
    }

    /**
     * @return array<int, string>
     */
    public function methodOptions(): array
    {
        return self::METHOD_OPTIONS;
    }

    /**
     * @return array<int, string>
     */
    public function statusOptions(): array
    {
        return self::STATUS_OPTIONS;
    }

    public function create(Request $request): void
    {
        $validated = $this->validate($request);

        Payment::query()->create($validated);
    }

    public function update(Request $request, Payment $payment): void
    {
        $validated = $this->validate($request);

        $payment->update($validated);
    }

    public function delete(Payment $payment): void
    {
        $payment->delete();
    }

    private function validate(Request $request): array
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'method' => ['required', Rule::in(self::METHOD_OPTIONS)],
            'type' => ['required', Rule::in(['full'])],
            'status' => ['required', Rule::in(self::STATUS_OPTIONS)],
            'amount' => ['required', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'paid_at' => ['nullable', 'date'],
        ]);

        $validated['paid_at'] = $validated['paid_at'] ?? ($validated['status'] === 'paid' ? now() : null);

        return $validated;
    }
}
