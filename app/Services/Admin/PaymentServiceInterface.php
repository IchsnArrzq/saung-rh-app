<?php

namespace App\Services\Admin;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface PaymentServiceInterface
{
    public const METHOD_OPTIONS = ['cash', 'qris', 'debit_card', 'credit_card', 'transfer', 'ewallet'];

    public const STATUS_OPTIONS = ['pending', 'paid', 'failed', 'refunded'];

    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator;

    public function orders(): Collection;

    /**
     * @return array<int, string>
     */
    public function methodOptions(): array;

    /**
     * @return array<int, string>
     */
    public function statusOptions(): array;

    public function create(Request $request): void;

    public function update(Request $request, Payment $payment): void;

    public function delete(Payment $payment): void;
}
