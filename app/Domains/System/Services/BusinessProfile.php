<?php

namespace App\Domains\System\Services;

use Illuminate\Support\Str;

/**
 * The restaurant's own identity — name, tagline, contact details — as the admin
 * last saved it on Pengaturan Aplikasi.
 *
 * Every screen used to spell the name out ("SaungRH" in the navs and on the
 * receipt, "CR Cafe & Resto" in the layouts), so renaming the business meant
 * editing a dozen Blade files and deploying. This reads the `profile`, `social`
 * and `contact` settings instead, and is shared into every view as `$business`
 * by AppServiceProvider.
 *
 * A Service rather than a QueryUseCase for the same reason AppSettings is one:
 * it wraps a cached configuration read that happens on every request and
 * carries no business rule (AGENTS.md § Configuration).
 */
class BusinessProfile
{
    /**
     * Where a home-screen label stops being a label and starts being a
     * sentence. `short_name` in a web manifest is capped around this by
     * convention, and iOS truncates past it anyway.
     */
    private const SHORT_NAME_LIMIT = 12;

    public function __construct(private readonly AppSettings $settings) {}

    public function name(): string
    {
        return $this->value('app.name', (string) config('app.name', 'Resto App'));
    }

    public function tagline(): string
    {
        return $this->value('app.tagline', 'Smart Cafe & Resto Management');
    }

    public function address(): string
    {
        return $this->value('contact.address', '');
    }

    public function phone(): string
    {
        return $this->value('contact.phone', '');
    }

    public function email(): string
    {
        return $this->value('contact.email', '');
    }

    public function hours(): string
    {
        return $this->value('contact.hours', '');
    }

    public function instagram(): string
    {
        return $this->value('social.instagram', '');
    }

    /**
     * Name trimmed to fit a home-screen label / PWA `short_name`.
     */
    public function shortName(): string
    {
        $name = $this->name();

        if (Str::length($name) <= self::SHORT_NAME_LIMIT) {
            return $name;
        }

        // Cut on a word boundary: "CR Cafe" beats the "CR Cafe & Re" that a
        // plain character limit would produce.
        $short = trim(Str::words($name, 2, ''));

        return $short !== '' ? $short : $name;
    }

    /**
     * Two-character monogram for the square avatar the navs put beside the name.
     */
    public function initials(): string
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', $this->name(), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($words === []) {
            return '--';
        }

        // A name that opens on its own acronym ("CR Cafe & Resto", "RH Kopi")
        // already carries the mark everyone recognises; one-letter-per-word
        // would turn that first case into "CC".
        $first = $words[0];

        if (Str::length($first) >= 2 && Str::upper($first) === $first) {
            return Str::upper(Str::substr($first, 0, 2));
        }

        return Str::upper(Str::substr($first, 0, 1).Str::substr($words[1] ?? Str::substr($first, 1, 1), 0, 1));
    }

    /**
     * A blank setting is a setting the admin cleared, not a value — fall back
     * so a stray empty row can never blank out the header on every page.
     */
    private function value(string $key, string $fallback): string
    {
        $value = trim((string) $this->settings->get($key, ''));

        return $value !== '' ? $value : $fallback;
    }
}
