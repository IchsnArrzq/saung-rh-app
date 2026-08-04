<?php

namespace App\Domains\Menu\Enums;

/**
 * Whether a menu item can currently be sold.
 *
 * Replaces the `menu_statuses` table. Named "availability" rather than
 * "status" because the model already exposes `$menu->status` as the backing
 * column — and because that is what it actually describes.
 *
 * Note the admin form only ever produced `available` / `unavailable` (it was a
 * boolean toggle); `sold_out` and `seasonal` were seeded but unreachable from
 * the UI and unused by any row. They are kept here so the meaning is not lost
 * if the kitchen ever wants them back.
 */
enum MenuAvailability: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
    case SoldOut = 'sold_out';
    case Seasonal = 'seasonal';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Unavailable => 'Tidak Tersedia',
            self::SoldOut => 'Habis',
            self::Seasonal => 'Musiman',
        };
    }

    /** daisyUI semantic token — see docs/DAISYUI-BLUEPRINT.md. */
    public function color(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Unavailable => 'error',
            self::SoldOut => 'warning',
            self::Seasonal => 'info',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::Available => 1,
            self::Unavailable => 2,
            self::SoldOut => 3,
            self::Seasonal => 4,
        };
    }

    public static function default(): self
    {
        return self::Available;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Only an available item may be ordered — mirrors the old scopeAvailable. */
    public function isSellable(): bool
    {
        return $this === self::Available;
    }

    /** Maps the admin form's boolean toggle onto the enum. */
    public static function fromToggle(bool $available): self
    {
        return $available ? self::Available : self::Unavailable;
    }
}
