<?php

namespace App\Domains\Payment\QueryUseCases;

use App\Domains\Payment\Repositories\PaymentRepository;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetPaymentListQueryUseCase
{
    public function __construct(private readonly PaymentRepository $payments) {}

    public function handle(string $search = '', int $perPage = 12): LengthAwarePaginator
    {
        return $this->payments->paginateForAdmin($perPage, $search);
    }

    /** Satu baris pembayaran — cukup untuk diadili Policy sebelum aksi jalan. */
    public function find(string $id): ?Payment
    {
        return $this->payments->find($id);
    }
}
