<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialRequest extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * @var array<string, string>
     */
    public const CATEGORIES = [
        'service' => 'Pelayanan',
        'kitchen' => 'Dapur',
        'ambience' => 'Suasana',
        'celebration' => 'Perayaan',
        'other' => 'Lainnya',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'table_session_id',
        'table_id',
        'table_code',
        'requested_by',
        'category',
        'description',
        'is_paid',
        'price',
        'status',
        'approved_by',
        'assigned_to',
        'handled_at',
    ];

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'price' => 'decimal:2',
            'handled_at' => 'datetime',
        ];
    }

    public function tableSession(): BelongsTo
    {
        return $this->belongsTo(TableSession::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * @param  Builder<SpecialRequest>  $query
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Requests a waiter still needs to act on.
     *
     * @param  Builder<SpecialRequest>  $query
     */
    public function scopeOpenFor(Builder $query, string $waiterId): Builder
    {
        return $query->where('assigned_to', $waiterId)->where('status', 'assigned');
    }
}
