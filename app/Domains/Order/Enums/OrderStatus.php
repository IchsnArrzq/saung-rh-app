<?php

namespace App\Domains\Order\Enums;

/**
 * Order lifecycle states.
 *
 * Hardcoded here rather than driven from a database table: this is a
 * single-tenant system, so the flow only changes with a deploy (see
 * AGENTS.md § State Machine). Replaces the old duplicated
 * `STATUS_OPTIONS` arrays that lived in both OrderService and
 * Livewire\Admin\Orders\Form.
 */
enum OrderStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Served = 'served';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    /** Indonesian label shown in the UI. */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Confirmed => 'Dikonfirmasi',
            self::Preparing => 'Disiapkan',
            self::Ready => 'Siap',
            self::Served => 'Disajikan',
            self::Paid => 'Lunas',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /** daisyUI semantic token — never a hardcoded colour (see docs/DAISYUI-BLUEPRINT.md). */
    public function color(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Confirmed => 'info',
            self::Preparing => 'warning',
            self::Ready => 'accent',
            self::Served => 'secondary',
            self::Paid => 'success',
            self::Cancelled => 'error',
        };
    }

    /**
     * Backing values — drop-in replacement for the old STATUS_OPTIONS const.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Terminal states: the order is done, nothing further can happen. */
    public function isFinal(): bool
    {
        return in_array($this, [self::Paid, self::Cancelled], true);
    }

    /** Still owes money / still in play — the cashier's open-bill worklist. */
    public function isOpen(): bool
    {
        return ! $this->isFinal();
    }

    /** States the kitchen display should react to. */
    public function isKitchenBound(): bool
    {
        return in_array($this, [self::Confirmed, self::Preparing], true);
    }

    /**
     * The order is live on the floor: submitted to the kitchen, not yet settled
     * or cancelled. Excludes drafts, which nobody is working on yet.
     */
    public function isInService(): bool
    {
        return in_array($this, [self::Confirmed, self::Preparing, self::Ready, self::Served], true);
    }

    /**
     * Backing values of the in-service states. Extracted because the same four
     * literals were repeated in three places (dashboard counters, the
     * receptionist table map and the waiter's tip log).
     *
     * @return array<int, string>
     */
    public static function inServiceValues(): array
    {
        return array_values(array_map(
            fn (self $status) => $status->value,
            array_filter(self::cases(), fn (self $status) => $status->isInService()),
        ));
    }

    /**
     * Allowed transitions. The forward chain is strict, but an open order can
     * always be settled or cancelled — the cashier may take payment at any
     * point (POS pays upfront, dine-in pays at the end).
     */
    public function canTransitionTo(self $next): bool
    {
        if ($this === $next) {
            return true;
        }

        if ($this->isFinal()) {
            return false;
        }

        if (in_array($next, [self::Paid, self::Cancelled], true)) {
            return true;
        }

        return in_array($next, $this->forwardStates(), true);
    }

    /**
     * @return array<int, self>
     */
    private function forwardStates(): array
    {
        return match ($this) {
            self::Draft => [self::Confirmed],
            // The kitchen may plate straight away without flagging "preparing".
            self::Confirmed => [self::Preparing, self::Ready],
            self::Preparing => [self::Ready],
            self::Ready => [self::Served],
            self::Served => [],
            self::Paid, self::Cancelled => [],
        };
    }
}
