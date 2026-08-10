<?php

namespace App\Domains\Employee\Enums;

/**
 * What a waiter did at the table.
 *
 * Replaces the `ServiceLog::TYPES` label map on the model. Like ShiftStatus the
 * column is a Postgres CHECK constraint, so the cases must match the database
 * exactly.
 */
enum ServiceType: string
{
    case Greeting = 'greeting';
    case Refill = 'refill';
    case Cleanup = 'cleanup';
    case SpecialRequest = 'special_request';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Greeting => 'Sambutan',
            self::Refill => 'Isi Ulang',
            self::Cleanup => 'Bersih-bersih',
            self::SpecialRequest => 'Permintaan Khusus',
            self::Other => 'Lainnya',
        };
    }

    public static function default(): self
    {
        return self::Greeting;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * value => label, the shape the select box and the log list expect.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
