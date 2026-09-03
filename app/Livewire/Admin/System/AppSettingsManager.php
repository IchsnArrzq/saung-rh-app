<?php

namespace App\Livewire\Admin\System;

use App\Domains\System\Repositories\AppSettingRepository;
use App\Domains\System\Services\AppSettings;
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

    public function mount(AppSettingRepository $settings): void
    {
        $this->values = collect($settings->allKeyValue())
            ->map(fn ($value) => (string) $value)
            ->all();
    }

    public function save(AppSettings $settings): void
    {
        $settings->setMany($this->values);

        session()->flash('success', 'Pengaturan aplikasi disimpan.');
    }

    public function render(AppSettingRepository $settings): View
    {
        return view('livewire.admin.system.app-settings-manager', [
            'groups' => $settings->groupedForAdmin(),
        ]);
    }
}
