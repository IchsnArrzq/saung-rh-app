<?php

namespace App\Models;

use App\Domains\Menu\Enums\MenuAvailability;
use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;
    use HasMedia;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'menu_category_id',
        // Backed by App\Domains\Menu\Enums\MenuAvailability. Plain string, not
        // an enum cast, so Blade comparisons keep working (same call as Order/Table).
        'status',
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
     * Primary display image URL: prefers the media library, falls back to the
     * legacy single image_url column so existing menus keep working.
     */
    public function getDisplayImageUrlAttribute(): ?string
    {
        return $this->primaryImage()?->url ?? ($this->image_url ?: null);
    }

    /**
     * Availability is derived from the status column, backed by
     * App\Domains\Menu\Enums\MenuAvailability.
     */
    public function getIsAvailableAttribute(): bool
    {
        return $this->status === MenuAvailability::Available->value;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Menu>  $query
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', MenuAvailability::Available->value);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Menu>  $query
     */
    public function scopeUnavailable($query)
    {
        return $query->where(fn ($inner) => $inner
            ->where('status', '!=', MenuAvailability::Available->value)
            ->orWhereNull('status'));
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
