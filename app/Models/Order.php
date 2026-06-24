<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cashier_id',
        'customer_id',
        'table_id',
        'order_number',
        'customer_name',
        'status',
        'notes',
        'subtotal',
        'discount',
        'tax',
        'total',
        'ordered_at',
    ];

    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function orderNotes(): HasMany
    {
        return $this->hasMany(OrderNote::class);
    }

    /**
     * VIP priority is derived from any item belonging to a VIP-track menu.
     * Falls back to the `vip_items_count` aggregate when present (cheaper for lists).
     */
    public function getIsVipAttribute(): bool
    {
        if (! is_null($this->getAttributeValue('vip_items_count'))) {
            return (int) $this->vip_items_count > 0;
        }

        if ($this->relationLoaded('items')) {
            return $this->items->contains(fn ($item) => optional($item->menu)->track === 'vip');
        }

        return false;
    }
}
