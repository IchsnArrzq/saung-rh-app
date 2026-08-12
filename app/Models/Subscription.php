<?php

namespace App\Models;

use App\Domains\System\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'plan',
        'license_key',
        'status',
        'seats',
        'started_at',
        'expires_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'seats' => 'integer',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * A licence is valid while it is active/trial and not past its expiry.
     */
    public function isValid(): bool
    {
        if (! $this->status->entitlesAccess()) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function daysRemaining(): ?int
    {
        if ($this->expires_at === null) {
            return null;
        }

        return (int) max(0, now()->startOfDay()->diffInDays($this->expires_at->startOfDay(), false));
    }
}
