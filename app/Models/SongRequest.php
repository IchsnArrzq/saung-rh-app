<?php

namespace App\Models;

use App\Domains\Social\Enums\SongStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SongRequest extends Model
{
    use HasFactory;
    use HasUuids;

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
            'status' => SongStatus::class,
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
        return $query->whereIn('status', SongStatus::activeValues());
    }
}
