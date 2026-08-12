<?php

namespace App\Domains\Customer\UseCases;

use App\Domains\Customer\Repositories\CustomerRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteCustomerUseCase
{
    public function __construct(private readonly CustomerRepositoryInterface $customers) {}

    public function handle(string $customerId): void
    {
        $customer = $this->customers->find($customerId);

        if (! $customer) {
            throw ValidationException::withMessages([
                'delete' => 'Pelanggan tidak ditemukan.',
            ]);
        }

        DB::transaction(fn () => $this->customers->delete($customer));
    }
}
