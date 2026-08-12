<?php

namespace App\Domains\Payment\Enums;

/**
 * Lifecycle of a single payment record.
 *
 * Unlike Table and Menu, this never lived in a lookup table — `payments.status`
 * has always been a plain string column, so no data migration was needed. The
 * values simply move out of PaymentService::STATUS_OPTIONS (and its duplicate
 * inside Livewire\Admin\Payments\Form) into one place.
 */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Paid => 'Lunas',
            self::Failed => 'Gagal',
            self::Refunded => 'Dikembalikan',
        };
    }

    /** daisyUI semantic token — see docs/DAISYUI-BLUEPRINT.md. */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Failed => 'error',
            self::Refunded => 'info',
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
     * Money actually received. PaymentObserver keys stock deduction off this
     * state, so treat it as the one that has side effects.
     */
    public function isSettled(): bool
    {
        return $this === self::Paid;
    }
}
