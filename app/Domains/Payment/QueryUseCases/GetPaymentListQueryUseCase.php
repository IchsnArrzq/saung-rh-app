<?php

namespace App\Domains\Payment\QueryUseCases;

use App\Domains\Payment\Repositories\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetPaymentListQueryUseCase
{
    public function __construct(private readonly PaymentRepositoryInterface $payments) {}

    public function handle(string $search = '', int $perPage = 12): LengthAwarePaginator
    {
        return $this->payments->paginateForAdmin($perPage, $search);
    }
}
