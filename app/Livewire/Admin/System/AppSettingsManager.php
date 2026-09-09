<?php

namespace App\Livewire\Admin\System;

use App\Domains\System\Repositories\AppSettingRepository;
use App\Domains\System\Services\AppSettings;
use App\Models\AppSetting;
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
        $this->authorize('viewAny', AppSetting::class);

        foreach ($settings->groupedForAdmin()->flatten() as $setting) {
            Arr::set($this->values, $setting->key, (string) $setting->value);
        }
    }

    public function save(AppSettingRepository $repository, AppSettings $settings): void
    {
        $payload = [];
        $rows = $repository->groupedForAdmin()->flatten();

        // Diperiksa lagi di sini, bukan hanya di mount(): save() adalah request
        // HTTP tersendiri. Ditaruh di depan supaya penolakan tetap 403 walau
        // payload-nya kebetulan kosong — AppSettingPolicy menilai izin, bukan
        // baris, jadi satu baris mana pun mewakili.
        if ($first = $rows->first()) {
            $this->authorize('update', $first);
        }

        // Drive the write from the rows this form actually renders rather than
        // from $values: it is a public property, so its shape is whatever the
        // browser last sent, and iterating it would let the form create rows.
        // Rows the form does not render — the logo and mark uploads — stay out
        // of the payload as well, so saving here never touches them.
        foreach ($rows as $setting) {
            $value = Arr::get($this->values, $setting->key);

            if (is_scalar($value)) {
                $payload[$setting->key] = (string) $value;
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
