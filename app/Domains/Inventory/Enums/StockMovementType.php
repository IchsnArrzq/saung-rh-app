<?php

namespace App\Domains\Inventory\Enums;

/**
 * Direction of a stock ledger entry.
 *
 * `in` and `out` shift the balance by a delta; `adjustment` sets it outright
 * from a physical count, so its delta can point either way.
 */
enum StockMovementType: string
{
    case In = 'in';
    case Out = 'out';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Masuk',
            self::Out => 'Keluar',
            self::Adjustment => 'Koreksi',
        };
    }

    /** daisyUI semantic token — see docs/DAISYUI-BLUEPRINT.md. */
    public function color(): string
    {
        return match ($this) {
            self::In => 'success',
            self::Out => 'error',
            self::Adjustment => 'info',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
