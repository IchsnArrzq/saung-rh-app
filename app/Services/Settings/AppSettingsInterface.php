<?php

namespace App\Services\Settings;

interface AppSettingsInterface
{
    /**
     * All settings as a flat key => value map (cached).
     *
     * @return array<string, string|null>
     */
    public function all(): array;

    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, string $group = 'general', string $type = 'text'): void;

    /**
     * Bulk update a set of key => value pairs within a group.
     *
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values, string $group = 'general'): void;

    public function flush(): void;
}
