<?php

namespace App\Services\Settings;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;

class AppSettings
{
    private const CACHE_KEY = 'app_settings.all';

    /**
     * All settings as a flat key => value map (cached).
     *
     * @return array<string, string|null>
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => AppSetting::query()->pluck('value', 'key')->all());
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function set(string $key, mixed $value, string $group = 'general', string $type = 'text'): void
    {
        AppSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_null($value) ? null : (string) $value, 'group' => $group, 'type' => $type],
        );

        $this->flush();
    }

    /**
     * Bulk update a set of key => value pairs within a group.
     *
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            AppSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => is_null($value) ? null : (string) $value, 'group' => $group],
            );
        }

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
