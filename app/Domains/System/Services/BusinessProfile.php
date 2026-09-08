<?php

namespace App\Domains\System\Services;

use Illuminate\Support\Facades\Storage;
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

    /**
     * Settings keys behind the two brand images, and the disk they live on.
     * BrandAssets writes them; every layout reads them through here.
     */
    public const LOGO_KEY = 'brand.logo';

    public const MARK_KEY = 'brand.mark';

    public const DISK = 'public';

    /**
     * What the app ships with, used until an admin uploads their own.
     *
     * `logo` is the wordmark (header, login screen); `mark` is the square icon
     * (footer, home-screen icon) — the two are not interchangeable, a wordmark
     * squeezed into 40x40 is unreadable.
     */
    private const DEFAULT_LOGO = 'assets/logo-cr-cafe-resto.png';

    private const DEFAULT_MARK = 'assets/logo-cr-mark.png';

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

    public function logoUrl(): string
    {
        return $this->brandAsset(self::LOGO_KEY, self::DEFAULT_LOGO)['url'];
    }

    public function markUrl(): string
    {
        return $this->brandAsset(self::MARK_KEY, self::DEFAULT_MARK)['url'];
    }

    /**
     * Absolute filesystem path of the same file — the web manifest has to open
     * the icon to state its real pixel size.
     */
    public function logoPath(): string
    {
        return $this->brandAsset(self::LOGO_KEY, self::DEFAULT_LOGO)['path'];
    }

    public function markPath(): string
    {
        return $this->brandAsset(self::MARK_KEY, self::DEFAULT_MARK)['path'];
    }

    /**
     * Has an admin uploaded this one, or are we still showing the packaged
     * file? The navs answer it to decide between the mark and the monogram.
     */
    public function hasCustomLogo(): bool
    {
        return $this->brandAsset(self::LOGO_KEY, self::DEFAULT_LOGO)['custom'];
    }

    public function hasCustomMark(): bool
    {
        return $this->brandAsset(self::MARK_KEY, self::DEFAULT_MARK)['custom'];
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
     * Resolve one brand slot to a URL, a filesystem path, and whether it is
     * the admin's own file.
     *
     * The file is checked, not just the setting: the row outlives the file (a
     * database restored onto an empty storage folder, a file removed by hand),
     * and without this every page in every portal would render a broken image
     * with no way for the admin to see why.
     *
     * @return array{url: string, path: string, custom: bool}
     */
    private function brandAsset(string $key, string $default): array
    {
        $path = trim((string) $this->settings->get($key, ''));
        $disk = Storage::disk(self::DISK);

        if ($path !== '' && $disk->exists($path)) {
            return ['url' => $disk->url($path), 'path' => $disk->path($path), 'custom' => true];
        }

        return ['url' => asset($default), 'path' => public_path($default), 'custom' => false];
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
