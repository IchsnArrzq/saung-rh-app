<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * @var array<int, string>
     */
    public const STATUSES = ['scheduled', 'completed', 'absent'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'shift_date',
        'starts_at',
        'ends_at',
        'position',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'shift_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<Shift>  $query
     */
    public function scopeForDate(Builder $query, mixed $date): Builder
    {
        return $query->whereDate('shift_date', $date);
    }
}
