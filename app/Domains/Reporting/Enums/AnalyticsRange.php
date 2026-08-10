<?php

namespace App\Domains\Reporting\Enums;

use Carbon\CarbonImmutable;

/**
 * The time window every analytics screen filters by.
 *
 * The same `match` block computing this start date was copy-pasted into
 * ManagerAnalyticsService and Livewire\Staff\Receptionist\TopAnalytics — two
 * screens sitting side by side in the same portal, free to drift apart. It
 * lives in Reporting because every domain's dashboards read it; Fase C9 will
 * build the rest of that domain around it.
 */
enum AnalyticsRange: string
{
    case Today = 'today';
    case Week = 'week';
    case Month = 'month';

    public function label(): string
    {
        return match ($this) {
            self::Today => 'Hari Ini',
            self::Week => 'Minggu Ini',
            self::Month => 'Bulan Ini',
        };
    }

    /** Where the window opens. Everything at or after this instant is counted. */
    public function startsAt(): CarbonImmutable
    {
        return match ($this) {
            self::Today => CarbonImmutable::now()->startOfDay(),
            self::Week => CarbonImmutable::now()->startOfWeek(),
            self::Month => CarbonImmutable::now()->startOfMonth(),
        };
    }

    /** The window a screen falls back to before the user picks one. */
    public static function default(): self
    {
        return self::Week;
    }

    /** Unknown input falls back to the default rather than throwing at a user. */
    public static function fromRequest(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::default();
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
