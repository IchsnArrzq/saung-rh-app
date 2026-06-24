<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceLog extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'waiter_id',
        'table_id',
        'order_id',
        'type',
        'description',
        'served_at',
    ];

    protected function casts(): array
    {
        return [
            'served_at' => 'datetime',
        ];
    }

    public const TYPES = [
        'greeting' => 'Sambutan',
        'refill' => 'Isi Ulang',
        'cleanup' => 'Bersih-bersih',
        'special_request' => 'Permintaan Khusus',
        'other' => 'Lainnya',
    ];

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
