<?php

namespace App\Domains\Social\Enums;

/**
 * Lifecycle of a guest's song request.
 *
 * Replaces `SongRequest::STATUSES` and `ACTIVE_STATUSES`. Backed by a Postgres
 * CHECK constraint, so these four cases must match the database exactly.
 */
enum SongStatus: string
{
    case Queued = 'queued';
    case Playing = 'playing';
    case Done = 'done';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Antre',
            self::Playing => 'Diputar',
            self::Done => 'Selesai',
            self::Rejected => 'Ditolak',
        };
    }

    /** daisyUI semantic token — see docs/DAISYUI-BLUEPRINT.md. */
    public function color(): string
    {
        return match ($this) {
            self::Queued => 'warning',
            self::Playing => 'success',
            self::Done => 'neutral',
            self::Rejected => 'error',
        };
    }

    /** Still occupying one of the table's queue slots. */
    public function isActive(): bool
    {
        return in_array($this, [self::Queued, self::Playing], true);
    }

    /**
     * @return array<int, string>
     */
    public static function activeValues(): array
    {
        return array_values(array_map(
            fn (self $status) => $status->value,
            array_filter(self::cases(), fn (self $status) => $status->isActive()),
        ));
    }

    /** Terminal states — the request has left the queue for good. */
    public function isFinished(): bool
    {
        return ! $this->isActive();
    }

    /**
     * @return array<int, string>
     */
    public static function finishedValues(): array
    {
        return array_values(array_map(
            fn (self $status) => $status->value,
            array_filter(self::cases(), fn (self $status) => $status->isFinished()),
        ));
    }

    /**
     * The next step when the DJ hits "advance": queued → playing → done.
     * A finished request has nowhere left to go and stays put.
     */
    public function next(): self
    {
        return match ($this) {
            self::Queued => self::Playing,
            self::Playing => self::Done,
            self::Done, self::Rejected => $this,
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
