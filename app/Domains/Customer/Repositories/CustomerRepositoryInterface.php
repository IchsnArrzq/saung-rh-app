<?php

namespace App\Domains\Customer\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * All Customer persistence and querying.
 *
 * Behind an interface like every other Repository — swapping the data source
 * and mocking in tests are real needs (AGENTS.md § Repository).
 */
interface CustomerRepositoryInterface
{
    public function find(string $id): ?Customer;

    /** Admin listing, newest first, searchable by name/code/phone/email. */
    public function paginateForAdmin(int $perPage = 15, string $search = ''): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Customer;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Customer $customer, array $attributes): Customer;

    public function delete(Customer $customer): void;
}
