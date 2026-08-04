<?php

namespace App\Domains\Payment\Repositories;

use App\Domains\Payment\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function find(string $id): ?Payment
    {
        return Payment::query()->find($id);
    }

    public function paginateForAdmin(int $perPage = 12, string $search = ''): LengthAwarePaginator
    {
        $search = trim($search);

        return Payment::query()
            ->with('order')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('method', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%')
                        ->orWhere('reference', 'like', '%'.$search.'%')
                        ->orWhereHas('order', fn (Builder $order) => $order->where('order_number', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $attributes): Payment
    {
        return Payment::query()->create($attributes);
    }

    public function update(Payment $payment, array $attributes): Payment
    {
        $payment->update($attributes);

        return $payment;
    }

    public function delete(Payment $payment): void
    {
        $payment->delete();
    }

    public function sumSettledForOrder(string $orderId): float
    {
        return (float) Payment::query()
            ->where('order_id', $orderId)
            ->where('status', PaymentStatus::Paid->value)
            ->sum('amount');
    }
}
