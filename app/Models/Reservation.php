<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * Table status key used to lock a table while it is reserved.
     */
    public const RESERVED_STATUS_KEY = 'reserved';

    /**
     * Reservation statuses that actively hold a table.
     *
     * @var array<int, string>
     */
    public const HOLDING_STATUSES = ['confirmed', 'seated'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'table_id',
        'customer_name',
        'phone',
        'pax',
        'reservation_at',
        'status',
        'deposit_amount',
        'deposit_paid_at',
        'hold_until',
        'released_at',
        'release_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reservation_at' => 'datetime',
            'deposit_amount' => 'decimal:2',
            'deposit_paid_at' => 'datetime',
            'hold_until' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Whether a confirmed deposit has been recorded.
     */
    public function getHasDepositAttribute(): bool
    {
        return ! is_null($this->deposit_paid_at);
    }

    /**
     * Pending holds whose deposit window has lapsed without a paid deposit.
     */
    public function scopeExpiredHolds(Builder $query): Builder
    {
        return $query->where('status', 'pending')
            ->whereNull('deposit_paid_at')
            ->whereNotNull('hold_until')
            ->where('hold_until', '<', now());
    }

    /**
     * Confirmed bookings that passed their grace window without a check-in.
     */
    public function scopeNoShowCandidates(Builder $query, int $graceMinutes): Builder
    {
        return $query->where('status', 'confirmed')
            ->where('reservation_at', '<', now()->subMinutes($graceMinutes));
    }

    /**
     * Lock the assigned table by flipping an available table to "reserved".
     */
    public function lockTable(): void
    {
        // NB: $this->table inside a model method resolves to Eloquent's protected
        // $table (the table name), so the relation must be fetched explicitly.
        $table = $this->getRelationValue('table');

        if (! $table || $table->status !== 'available') {
            return;
        }

        if ($reserved = TableStatus::query()->where('key', self::RESERVED_STATUS_KEY)->first()) {
            $table->update(['table_status_id' => $reserved->id]);
        }
    }

    /**
     * Release a reserved table back to the default status, unless another
     * active reservation still holds it.
     */
    public function releaseTable(): void
    {
        $table = $this->getRelationValue('table');

        if (! $table || $table->status !== self::RESERVED_STATUS_KEY) {
            return;
        }

        $stillHeld = static::query()
            ->where('table_id', $table->id)
            ->where('id', '!=', $this->id)
            ->whereIn('status', self::HOLDING_STATUSES)
            ->exists();

        if ($stillHeld) {
            return;
        }

        if ($default = TableStatus::query()->where('is_default', true)->first()) {
            $table->update(['table_status_id' => $default->id]);
        }
    }
}
