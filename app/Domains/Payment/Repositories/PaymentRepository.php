<?php

namespace App\Domains\Payment\Repositories;

use App\Domains\Payment\Enums\PaymentStatus;
use App\Models\Payment;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PaymentRepository
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

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Payment
    {
        return Payment::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
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

    /** Money actually banked in a window — the dashboard's revenue figure. */
    public function sumSettledBetween(CarbonInterface $start, CarbonInterface $end): float
    {
        return (float) Payment::query()
            ->where('status', PaymentStatus::Paid->value)
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');
    }

    /** How many distinct orders were settled in a window. */
    public function countSettledOrdersBetween(CarbonInterface $start, CarbonInterface $end): int
    {
        return Payment::query()
            ->where('status', PaymentStatus::Paid->value)
            ->whereBetween('paid_at', [$start, $end])
            ->distinct('order_id')
            ->count('order_id');
    }

    /**
     * Settled payments for one day grouped by method, biggest first. Rows carry
     * `method`, `total_count` and `total_amount`.
     *
     * @return Collection<int, Payment>
     */
    public function methodBreakdownForDate(CarbonInterface $date): Collection
    {
        return Payment::query()
            ->selectRaw('method, COUNT(*) as total_count, SUM(amount) as total_amount')
            ->where('status', PaymentStatus::Paid->value)
            ->whereDate('paid_at', $date)
            ->groupBy('method')
            ->orderByDesc('total_amount')
            ->get();
    }
}
