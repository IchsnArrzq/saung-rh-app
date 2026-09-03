<?php

namespace App\Domains\Customer\UseCases;

use App\Domains\Customer\DTO\CustomerData;
use App\Domains\Customer\Repositories\CustomerRepository;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

/**
 * Registers a new customer in the master data.
 *
 * Uniqueness of `code` is enforced by the database index and by the form's
 * validation rules; there is no extra rule to run here, so the UseCase is thin
 * on purpose — its job is being the single place a customer row is born.
 */
class CreateCustomerUseCase
{
    public function __construct(private readonly CustomerRepository $customers) {}

    public function handle(CustomerData $data): Customer
    {
        return DB::transaction(fn (): Customer => $this->customers->create($data->toAttributes()));
    }
}
