<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SongRequest extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * Statuses that still occupy a slot in a table's queue.
     *
     * @var array<int, string>
     */
    public const ACTIVE_STATUSES = ['queued', 'playing'];

    /**
     * @var array<int, string>
     */
    public const STATUSES = ['queued', 'playing', 'done', 'rejected'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'table_session_id',
        'table_id',
        'table_code',
        'title',
        'artist',
        'requested_by',
        'status',
        'played_at',
    ];

    protected function casts(): array
    {
        return [
            'played_at' => 'datetime',
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

    /**
     * @param  Builder<SongRequest>  $query
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }
}
