<?php

namespace App\Domains\Inventory\Enums;

/**
 * Posting state of an inventory document.
 *
 * Deliberately shared by Purchase, Sale and StockOpname: all three were created
 * with the identical `$table->enum('status', ['draft', 'posted'])`, behave the
 * same way (a draft can be edited; posting writes stock movements and freezes
 * the document), and there is no reason for three copies of two values.
 *
 * ⚠️ Backed by a database CHECK constraint on each of those tables — adding a
 * case here needs a matching migration.
 */
enum DocumentStatus: string
{
    case Draft = 'draft';
    case Posted = 'posted';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Posted => 'Diposting',
        };
    }

    /** daisyUI semantic token — see docs/DAISYUI-BLUEPRINT.md. */
    public function color(): string
    {
        return match ($this) {
            self::Draft => 'warning',
            self::Posted => 'success',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Posted documents have already moved stock and must not be posted twice. */
    public function isPosted(): bool
    {
        return $this === self::Posted;
    }

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }
}
