<?php

namespace App\Livewire\Admin\System;

use App\Models\AppSetting;
use App\Services\Settings\AppSettings;
use Illuminate\View\View;
use Livewire\Component;

class AppSettingsManager extends Component
{
    /**
     * key => value map bound to the form inputs.
     *
     * @var array<string, string>
     */
    public array $values = [];

    public function mount(): void
    {
        $this->values = AppSetting::query()->pluck('value', 'key')->map(fn ($v) => (string) $v)->all();
    }

    public function save(AppSettings $settings): void
    {
        $settings->setMany($this->values);

        session()->flash('success', 'Pengaturan aplikasi disimpan.');
    }

    public function render(): View
    {
        $groups = AppSetting::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        return view('livewire.admin.system.app-settings-manager', [
            'groups' => $groups,
        ]);
    }
}
