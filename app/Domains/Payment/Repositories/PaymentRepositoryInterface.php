<?php

namespace App\Domains\Payment\Repositories;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
}
