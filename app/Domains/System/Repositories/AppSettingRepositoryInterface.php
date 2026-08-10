<?php

namespace App\Domains\System\Repositories;

use Illuminate\Support\Collection;

interface AppSettingRepositoryInterface
{
    /**
     * Every setting as a flat key => value map.
     *
     * @return array<string, string|null>
     */
    public function allKeyValue(): array;

    /**
     * Settings grouped by their `group` column, for the admin form's sections.
     *
     * @return Collection<string, Collection<int, \App\Models\AppSetting>>
     */
    public function groupedForAdmin(): Collection;

    public function upsert(string $key, ?string $value, ?string $group = null, ?string $type = null): void;
}
