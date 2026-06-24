<?php

namespace App\Models;

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
        'status',
        'visibility',
        'is_anonymous',
        'pax',
        'customer_name',
        'started_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'pax' => 'integer',
            'started_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function visitorLogs(): HasMany
    {
        return $this->hasMany(VisitorLog::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<TableSession>  $query
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
