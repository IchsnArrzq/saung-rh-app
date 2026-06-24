<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'menu_category_id',
        'menu_status_id',
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'image_url',
        'track',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    /**
     * Availability is derived from the menu status (single source of truth).
     */
    public function getIsAvailableAttribute(): bool
    {
        return optional($this->status)->key === 'available';
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Menu>  $query
     */
    public function scopeAvailable($query)
    {
        return $query->whereHas('status', fn ($q) => $q->where('key', 'available'));
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Menu>  $query
     */
    public function scopeUnavailable($query)
    {
        return $query->whereDoesntHave('status', fn ($q) => $q->where('key', 'available'));
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Menu>  $query
     */
    public function scopeVip($query)
    {
        return $query->where('track', 'vip');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(MenuStatus::class, 'menu_status_id');
    }

    public function menuIngredients(): HasMany
    {
        return $this->hasMany(MenuIngredient::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reservationItems(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }
}
