<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Table extends Model
{
    use HasFactory;
    use HasUuids;

    protected static function booted(): void
    {
        static::creating(function (Table $table): void {
            if (empty($table->qr_token)) {
                $table->qr_token = (string) Str::random(24);
            }
        });
    }

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'capacity',
        // Backed by App\Domains\Table\Enums\TableStatus. Left as a plain string
        // rather than an enum cast so Blade comparisons keep working — same
        // call as Order::$status.
        'status',
        'table_category_id',
        'qr_token',
        'position_x',
        'position_y',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'position_x' => 'integer',
            'position_y' => 'integer',
        ];
    }

    public function tableCategory(): BelongsTo
    {
        return $this->belongsTo(TableCategory::class, 'table_category_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function tableSessions(): HasMany
    {
        return $this->hasMany(TableSession::class);
    }

    public function visitorLogs(): HasMany
    {
        return $this->hasMany(VisitorLog::class);
    }

    public function activeSession(): ?TableSession
    {
        return $this->tableSessions()->active()->latest('started_at')->first();
    }
}
