<?php

namespace App\Domains\System\Repositories;

use App\Models\AppSetting;
use Illuminate\Support\Collection;

class AppSettingRepository
{
    /**
     * Settings whose value is a storage path, not text — the logo and the app
     * mark. They belong to their own upload card, never to the text form.
     */
    private const FILE_TYPE = 'image';

    public function findByKey(string $key): ?AppSetting
    {
        return AppSetting::query()->firstWhere('key', $key);
    }

    /**
     * Every setting as a flat key => value map.
     *
     * @return array<string, string|null>
     */
    public function allKeyValue(): array
    {
        return AppSetting::query()->pluck('value', 'key')->all();
    }

    /**
     * Settings the plain-text admin form owns, grouped by their `group` column
     * for the form's sections.
     *
     * File-backed settings are left out: their value is a storage path, and a
     * text input bound to one would hand the admin a field where a typo blanks
     * the logo on every page — worse, a form loaded before an upload would
     * write the stale path back over the new one on the next save.
     *
     * @return Collection<string, Collection<int, AppSetting>>
     */
    public function groupedForAdmin(): Collection
    {
        return AppSetting::query()
            ->where('type', '!=', self::FILE_TYPE)
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');
    }

    public function upsert(string $key, ?string $value, ?string $group = null, ?string $type = null): void
    {
        // Only overwrite group/type when the caller actually supplied them —
        // a bulk save from the settings form must not reset a row's metadata.
        $attributes = array_filter(
            ['value' => $value, 'group' => $group, 'type' => $type],
            fn ($item, $key) => $key === 'value' || $item !== null,
            ARRAY_FILTER_USE_BOTH,
        );

        AppSetting::query()->updateOrCreate(['key' => $key], $attributes);
    }
}
