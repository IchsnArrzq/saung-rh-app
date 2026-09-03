<?php

namespace App\Domains\System\Services;

use App\Domains\System\Repositories\AppSettingRepository;
use Illuminate\Support\Facades\Cache;

/**
 * Runtime configuration stored in the database and cached forever.
 *
 * Stays a Service rather than becoming UseCases: reading a setting happens on
 * nearly every request and writing one carries no business rule, so wrapping
 * either in a UseCase would add ceremony and nothing else (AGENTS.md
 * § Configuration).
 */
class AppSettings
{
    private const CACHE_KEY = 'app_settings.all';

    public function __construct(private readonly AppSettingRepository $settings) {}

    /**
     * All settings as a flat key => value map (cached).
     *
     * @return array<string, string|null>
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => $this->settings->allKeyValue());
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function set(string $key, mixed $value, string $group = 'general', string $type = 'text'): void
    {
        $this->settings->upsert($key, $this->stringify($value), $group, $type);

        $this->flush();
    }

    /**
     * Bulk update a set of key => value pairs.
     *
     * Group and type are left untouched here: the admin form posts back every
     * key at once, and stamping them all with one group would flatten the
     * sections the form itself is built from.
     *
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->settings->upsert($key, $this->stringify($value));
        }

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function stringify(mixed $value): ?string
    {
        return is_null($value) ? null : (string) $value;
    }
}
