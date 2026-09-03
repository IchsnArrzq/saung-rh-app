<?php

namespace App\Domains\Customer\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * All Customer persistence and querying.
 */
class CustomerRepository
{
    public function find(string $id): ?Customer
    {
        return Customer::query()->find($id);
    }

    /** Admin listing, newest first, searchable by name/code/phone/email. */
    public function paginateForAdmin(int $perPage = 15, string $search = ''): LengthAwarePaginator
    {
        $search = trim($search);

        return Customer::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Customer
    {
        return Customer::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Customer $customer, array $attributes): Customer
    {
        $customer->update($attributes);

        return $customer;
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}
