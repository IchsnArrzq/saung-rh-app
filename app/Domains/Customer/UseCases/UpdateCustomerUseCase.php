<?php

namespace App\Domains\Customer\UseCases;

use App\Domains\Customer\DTO\CustomerData;
use App\Domains\Customer\Repositories\CustomerRepository;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class UpdateCustomerUseCase
{
    public function __construct(private readonly CustomerRepository $customers) {}

    public function handle(Customer $customer, CustomerData $data): Customer
    {
        return DB::transaction(fn (): Customer => $this->customers->update($customer, $data->toAttributes()));
    }
}
