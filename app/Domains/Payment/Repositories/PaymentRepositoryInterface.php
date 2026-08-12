<?php

namespace App\Domains\Payment\Repositories;

use App\Models\Payment;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PaymentRepositoryInterface
{
    public function find(string $id): ?Payment;

    public function paginateForAdmin(int $perPage = 12, string $search = ''): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Payment;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Payment $payment, array $attributes): Payment;

    public function delete(Payment $payment): void;

    public function sumSettledForOrder(string $orderId): float;

    /** Money actually banked in a window — the dashboard's revenue figure. */
    public function sumSettledBetween(CarbonInterface $start, CarbonInterface $end): float;

    /** How many distinct orders were settled in a window. */
    public function countSettledOrdersBetween(CarbonInterface $start, CarbonInterface $end): int;

    /**
     * Settled payments for one day grouped by method, biggest first. Rows carry
     * `method`, `total_count` and `total_amount`.
     *
     * @return Collection<int, Payment>
     */
    public function methodBreakdownForDate(CarbonInterface $date): Collection;
}
