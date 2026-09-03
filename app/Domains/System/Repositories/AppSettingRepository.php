<?php

namespace App\Domains\System\Repositories;

use App\Models\AppSetting;
use Illuminate\Support\Collection;

class AppSettingRepository
{
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
     * Settings grouped by their `group` column, for the admin form's sections.
     *
     * @return Collection<string, Collection<int, AppSetting>>
     */
    public function groupedForAdmin(): Collection
    {
        return AppSetting::query()
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
