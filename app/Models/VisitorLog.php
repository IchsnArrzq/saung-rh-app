<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorLog extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'table_id',
        'table_session_id',
        'recorded_by',
        'source',
        'pax',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'pax' => 'integer',
            'visited_at' => 'datetime',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function tableSession(): BelongsTo
    {
        return $this->belongsTo(TableSession::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
