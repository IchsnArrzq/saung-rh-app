<?php

namespace App\Domains\Customer\QueryUseCases;

use App\Domains\Customer\Repositories\CustomerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Read-only listing for the admin customer table.
 */
class GetCustomerListQueryUseCase
{
    public function __construct(private readonly CustomerRepository $customers) {}

    public function handle(string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return $this->customers->paginateForAdmin($perPage, $search);
    }
}
