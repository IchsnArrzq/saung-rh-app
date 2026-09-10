<?php

namespace App\Domains\System\Repositories;

use App\Models\PaymentAccount;
use Illuminate\Database\Eloquent\Collection;

class PaymentAccountRepository
{
    /**
     * Rekening aktif dari tipe yang diminta, urut seperti yang diatur admin.
     *
     * @param  array<int, string>  $types  kunci PaymentAccount::TYPES
     * @return Collection<int, PaymentAccount>
     */
    public function activeOfTypes(array $types): Collection
    {
        return PaymentAccount::query()
            ->active()
            ->whereIn('type', $types)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }
}
