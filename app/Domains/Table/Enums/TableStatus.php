<?php

namespace App\Domains\Table\Enums;

/**
 * Dining table availability states.
 *
 * Replaces the old `table_statuses` database table. Values, labels, colours and
 * ordering are the ones that were seeded there — the table only ever held these
 * five rows, and four of them were already protected from deletion by
 * TableStatusService::RESERVED_KEYS, which is the clearest sign the flow was
 * never really data-driven (AGENTS.md § State Machine).
 */
enum TableStatus: string
{
    case Available = 'available';
    case Occupied = 'occupied';
    case OrderIn = 'order_in';
    case Reserved = 'reserved';
    case Cleaning = 'cleaning';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Occupied => 'Terisi',
            self::OrderIn => 'Pesanan Masuk',
            self::Reserved => 'Direservasi',
            self::Cleaning => 'Perlu Dibersihkan',
        };
    }

    /** daisyUI semantic token — see docs/DAISYUI-BLUEPRINT.md. */
    public function color(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Occupied => 'error',
            self::OrderIn => 'warning',
            self::Reserved => 'secondary',
            self::Cleaning => 'info',
        };
    }

    /** Display order on the status board, mirroring the old sort_order column. */
    public function sortOrder(): int
    {
        return match ($this) {
            self::Available => 1,
            self::Occupied => 2,
            self::OrderIn => 3,
            self::Reserved => 4,
            self::Cleaning => 5,
        };
    }

    /** The state a brand-new table starts in (was `is_default` on the row). */
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

    /**
     * Guests may keep ordering on these — a party already seated can run several
     * rounds. Replaces OrderCartService::ORDERABLE_STATUSES.
     */
    public function isOrderable(): bool
    {
        return in_array($this, [self::Available, self::Occupied, self::OrderIn], true);
    }

    /**
     * Backing values of the orderable states, for `whereIn` filters.
     *
     * @return array<int, string>
     */
    public static function orderableValues(): array
    {
        return array_values(array_map(
            fn (self $status) => $status->value,
            array_filter(self::cases(), fn (self $status) => $status->isOrderable()),
        ));
    }

    /** Only a free table can be picked or booked. */
    public function isFree(): bool
    {
        return $this === self::Available;
    }

    // Note: deliberately no transition guard. Unlike an order, a table may move
    // between any two states — staff correct the floor by hand and the old UI
    // let them pick freely. Adding rules here would be a behaviour change, not
    // a safeguard.
}
