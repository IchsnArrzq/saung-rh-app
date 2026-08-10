<?php

namespace App\Domains\System\Enums;

/**
 * State of the installation's licence.
 *
 * Replaces `Subscription::STATUSES`. Backed by a Postgres CHECK constraint, so
 * these four cases must match the database exactly.
 */
enum SubscriptionStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case Expired = 'expired';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Trial => 'Uji Coba',
            self::Active => 'Aktif',
            self::Expired => 'Kedaluwarsa',
            self::Suspended => 'Ditangguhkan',
        };
    }

    /** daisyUI semantic token — see docs/DAISYUI-BLUEPRINT.md. */
    public function color(): string
    {
        return match ($this) {
            self::Trial => 'info',
            self::Active => 'success',
            self::Expired => 'error',
            self::Suspended => 'warning',
        };
    }

    /** States that still entitle the installation to run (before checking expiry). */
    public function entitlesAccess(): bool
    {
        return in_array($this, [self::Active, self::Trial], true);
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
