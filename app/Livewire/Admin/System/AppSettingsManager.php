<?php

namespace App\Livewire\Admin\System;

use App\Domains\System\Repositories\AppSettingRepository;
use App\Domains\System\Services\AppSettings;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Livewire\Component;

class AppSettingsManager extends Component
{
    /**
     * Settings nested by their dotted key, e.g. `app.name` lives at
     * $values['app']['name'].
     *
     * Setting keys are namespaced with dots, and `wire:model="values.app.name"`
     * resolves that path *nested* -- bound against a flat `['app.name' => ...]`
     * map it reads nothing, which left every field on the form blank and made
     * saving write the values back under the wrong shape. The form binds to the
     * nested array; save() flattens it again on the way out.
     *
     * @var array<string, mixed>
     */
    public array $values = [];

    public function mount(AppSettingRepository $settings): void
    {
        foreach ($settings->allKeyValue() as $key => $value) {
            Arr::set($this->values, $key, (string) $value);
        }
    }

    public function save(AppSettingRepository $repository, AppSettings $settings): void
    {
        $payload = [];

        // Drive the write from the keys that exist in the database rather than
        // from $values: it is a public property, so its shape is whatever the
        // browser last sent, and iterating it would let the form create rows.
        foreach (array_keys($repository->allKeyValue()) as $key) {
            $value = Arr::get($this->values, $key);

            if (is_scalar($value)) {
                $payload[$key] = (string) $value;
            }
        }

        $settings->setMany($payload);

        session()->flash('success', 'Pengaturan aplikasi disimpan.');
    }

    public function render(AppSettingRepository $settings): View
    {
        return view('livewire.admin.system.app-settings-manager', [
            'groups' => $settings->groupedForAdmin(),
        ]);
    }
}
