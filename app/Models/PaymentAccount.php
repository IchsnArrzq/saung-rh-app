<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * @var array<string, string>
     */
    public const TYPES = [
        'bank' => 'Transfer Bank',
        'ewallet' => 'E-Wallet',
        'qris' => 'QRIS',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'label',
        'type',
        'provider',
        'account_number',
        'account_holder',
        'instructions',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<PaymentAccount>  $query
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
