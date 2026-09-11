<?php

namespace App\Domains\Table\Enums;

/**
 * Lifecycle of a QR table session.
 *
 * Pending  — a guest scanned the table's QR and gave a name; nothing is unlocked
 *            until a cashier or receptionist confirms they are really seated.
 * Active   — confirmed: ordering, chat, songs and requests work for this table.
 * Closed   — the bill was settled, staff freed the table, or staff ended it.
 * Rejected — staff turned the scan down (typically: nobody is at that table).
 */
enum TableSessionStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Closed = 'closed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu konfirmasi',
            self::Active => 'Aktif',
            self::Closed => 'Selesai',
            self::Rejected => 'Ditolak',
        };
    }

    /** daisyUI semantic token — see docs/DAISYUI-BLUEPRINT.md. */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Active => 'success',
            self::Closed => 'neutral',
            self::Rejected => 'error',
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Pending => in_array($next, [self::Active, self::Rejected, self::Closed], true),
            self::Active => $next === self::Closed,
            self::Closed, self::Rejected => false,
        };
    }

    /** A live session still holds its table — the guest is waiting or seated. */
    public function isLive(): bool
    {
        return in_array($this, [self::Pending, self::Active], true);
    }

    /**
     * @return array<int, string>
     */
    public static function liveValues(): array
    {
        return [self::Pending->value, self::Active->value];
    }

    /**
     * @return array<string, string> value => label, for filters
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $status) => $status->value, self::cases()),
            array_map(fn (self $status) => $status->label(), self::cases()),
        );
    }
}
