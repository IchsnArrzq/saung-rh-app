<?php

namespace App\Domains\Social\Enums;

/**
 * Lifecycle of a guest's special request, from the table to a waiter's hands.
 *
 * Backed by a Postgres CHECK constraint — these five cases must match the
 * database exactly.
 */
enum SpecialRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Assigned = 'assigned';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::Assigned => 'Ditugaskan',
            self::Done => 'Selesai',
        };
    }

    /** daisyUI semantic token — see docs/DAISYUI-BLUEPRINT.md. */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'info',
            self::Rejected => 'error',
            self::Assigned => 'primary',
            self::Done => 'success',
        };
    }

    /** Still somewhere in the pipeline — not yet rejected or finished. */
    public function isOpen(): bool
    {
        return ! in_array($this, [self::Rejected, self::Done], true);
    }

    /**
     * Everything past the manager's desk — what the "recent activity" list on
     * the approver board shows.
     *
     * @return array<int, string>
     */
    public static function handledValues(): array
    {
        return [
            self::Approved->value,
            self::Assigned->value,
            self::Done->value,
            self::Rejected->value,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
