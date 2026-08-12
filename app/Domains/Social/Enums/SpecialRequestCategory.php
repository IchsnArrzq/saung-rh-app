<?php

namespace App\Domains\Social\Enums;

/**
 * What kind of favour the guest is asking for.
 *
 * Replaces the `SpecialRequest::CATEGORIES` label map on the model — UI labels
 * do not belong on an Eloquent class. Backed by a Postgres CHECK constraint.
 */
enum SpecialRequestCategory: string
{
    case Service = 'service';
    case Kitchen = 'kitchen';
    case Ambience = 'ambience';
    case Celebration = 'celebration';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Service => 'Pelayanan',
            self::Kitchen => 'Dapur',
            self::Ambience => 'Suasana',
            self::Celebration => 'Perayaan',
            self::Other => 'Lainnya',
        };
    }

    /** Anything unrecognised lands in "other" rather than failing the guest. */
    public static function fromInput(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::Other;
    }

    public static function default(): self
    {
        return self::Service;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * value => label, the shape the category select box expects.
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
