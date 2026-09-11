<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Jenis permintaan khusus (Pelayanan, Dapur, Perayaan, …) — master data yang
 * dikelola admin dan ditampilkan sebagai pilihan di panel meja tamu.
 * Menggantikan enum SpecialRequestCategory yang dulu dikodekan.
 */
class SpecialRequestCategory extends Model
{
    use HasUuids;

    /** Ikon bila admin tidak memilih — juga dipakai baris lama tanpa kategori. */
    public const DEFAULT_ICON = 'ri-service-line';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
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

    public function specialRequests(): HasMany
    {
        return $this->hasMany(SpecialRequest::class);
    }

    /**
     * @param  Builder<SpecialRequestCategory>  $query
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<SpecialRequestCategory>  $query
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function iconClass(): string
    {
        return $this->icon ?: self::DEFAULT_ICON;
    }
}
