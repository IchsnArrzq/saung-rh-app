<?php

namespace App\Domains\Employee\Enums;

/**
 * Where a rostered shift ended up.
 *
 * Replaces `Shift::STATUSES`. The column was created with
 * `$table->enum('status', [...])`, which Postgres stores as a CHECK constraint
 * — so these three cases must stay in lockstep with the database. Adding a case
 * here without a migration fails at write time (the lesson from Fase C4).
 */
enum ShiftStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Absent = 'absent';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Dijadwalkan',
            self::Completed => 'Selesai',
            self::Absent => 'Tidak Hadir',
        };
    }

    /** daisyUI semantic token — see docs/DAISYUI-BLUEPRINT.md. */
    public function color(): string
    {
        return match ($this) {
            self::Scheduled => 'info',
            self::Completed => 'success',
            self::Absent => 'error',
        };
    }

    /** What a newly rostered shift starts as. */
    public static function default(): self
    {
        return self::Scheduled;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
