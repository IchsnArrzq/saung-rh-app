<?php

namespace App\Domains\Reservation\Enums;

/**
 * Booking lifecycle.
 *
 * ⚠️ These six values are also enforced by the database: the column was created
 * with `$table->enum('status', [...])`, which Postgres implements as a CHECK
 * constraint. Adding a case here without a matching migration will fail on
 * write — keep the two in step.
 */
enum ReservationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Seated = 'seated';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Konfirmasi',
            self::Confirmed => 'Dikonfirmasi',
            self::Seated => 'Sudah Duduk',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
            self::NoShow => 'Tidak Hadir',
        };
    }

    /** daisyUI semantic token — see docs/DAISYUI-BLUEPRINT.md. */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'info',
            self::Seated => 'success',
            self::Completed => 'neutral',
            self::Cancelled => 'error',
            self::NoShow => 'error',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Statuses that actively hold a table, so it must not be handed to anyone
     * else. Replaces Reservation::HOLDING_STATUSES.
     */
    public function isHolding(): bool
    {
        return in_array($this, [self::Confirmed, self::Seated], true);
    }

    /**
     * @return array<int, string>
     */
    public static function holdingValues(): array
    {
        return array_values(array_map(
            fn (self $status) => $status->value,
            array_filter(self::cases(), fn (self $status) => $status->isHolding()),
        ));
    }

    /** Nothing further happens to the booking. */
    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled, self::NoShow], true);
    }
}
