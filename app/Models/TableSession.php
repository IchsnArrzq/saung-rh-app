<?php

namespace App\Models;

use App\Domains\Table\Enums\TableSessionCloseReason;
use App\Domains\Table\Enums\TableSessionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableSession extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'table_id',
        'token',
        'join_code',
        'status',
        'visibility',
        'is_anonymous',
        'pax',
        'customer_name',
        'started_at',
        'approved_by',
        'approved_at',
        'closed_at',
        'closed_by',
        'close_reason',
    ];

    /** The join code is what keeps outsiders off a table — never let it ride along in a dump. */
    protected $hidden = [
        'token',
        'join_code',
    ];

    protected function casts(): array
    {
        return [
            'status' => TableSessionStatus::class,
            'close_reason' => TableSessionCloseReason::class,
            'is_anonymous' => 'boolean',
            'pax' => 'integer',
            'started_at' => 'datetime',
            'approved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function visitorLogs(): HasMany
    {
        return $this->hasMany(VisitorLog::class);
    }

    public function isActive(): bool
    {
        return $this->status === TableSessionStatus::Active;
    }

    public function isPending(): bool
    {
        return $this->status === TableSessionStatus::Pending;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<TableSession>  $query
     */
    public function scopeActive($query)
    {
        return $query->where('status', TableSessionStatus::Active->value);
    }

    /**
     * Waiting for the cashier or already seated — either way the table is held.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<TableSession>  $query
     */
    public function scopeLive($query)
    {
        return $query->whereIn('status', TableSessionStatus::liveValues());
    }
}
