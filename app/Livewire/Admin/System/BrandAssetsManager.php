<?php

namespace App\Livewire\Admin\System;

use App\Domains\System\Repositories\AppSettingRepository;
use App\Domains\System\Services\BrandAssets;
use App\Models\AppSetting;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Logo dan ikon aplikasi di Pengaturan Aplikasi.
 *
 * Dua slot terpisah karena dipakai di tempat yang berbeda: `logo` adalah
 * wordmark di header dan halaman masuk, `mark` adalah ikon persegi di footer,
 * navigasi, dan layar utama ponsel (PWA).
 */
class BrandAssetsManager extends Component
{
    use WithFileUploads;

    public $logo = null;

    public $mark = null;

    /** Maks 2 MB — ini gambar yang dimuat di setiap halaman, bukan foto menu. */
    private const MAX_KILOBYTES = 2048;

    public function mount(): void
    {
        $this->authorize('viewAny', AppSetting::class);
    }

    /**
     * `wire:model` mengunggah berkasnya begitu dipilih, jadi validasinya juga
     * di sini — bukan menunggu tombol simpan. Tanpa ini pratinjaunya sempat
     * dirender lebih dulu, dan berkas yang bukan gambar meledak di layar.
     */
    public function updatedLogo(): void
    {
        $this->validateSlot('logo');
    }

    public function updatedMark(): void
    {
        $this->validateSlot('mark');
    }

    public function saveLogo(BrandAssets $assets, AppSettingRepository $settings): void
    {
        $this->save('logo', $assets, $settings);
    }

    public function saveMark(BrandAssets $assets, AppSettingRepository $settings): void
    {
        $this->save('mark', $assets, $settings);
    }

    public function removeLogo(BrandAssets $assets, AppSettingRepository $settings): void
    {
        $this->remove('logo', $assets, $settings);
    }

    public function removeMark(BrandAssets $assets, AppSettingRepository $settings): void
    {
        $this->remove('mark', $assets, $settings);
    }

    public function render(AppSettingRepository $settings): View
    {
        return view('livewire.admin.system.brand-assets-manager', [
            'appSetting' => $this->settingRow($settings),
        ]);
    }

    private function save(string $slot, BrandAssets $assets, AppSettingRepository $settings): void
    {
        $this->authorize('update', $this->settingRow($settings));

        $this->validateSlot($slot);

        $assets->store($slot, $this->{$slot});

        $this->reset($slot);

        session()->flash('brand-status', $slot === 'logo' ? 'Logo diperbarui.' : 'Ikon diperbarui.');
    }

    private function remove(string $slot, BrandAssets $assets, AppSettingRepository $settings): void
    {
        $this->authorize('update', $this->settingRow($settings));

        $assets->clear($slot);

        $this->reset($slot);

        session()->flash(
            'brand-status',
            $slot === 'logo' ? 'Logo kembali ke bawaan aplikasi.' : 'Ikon kembali ke bawaan aplikasi.',
        );
    }

    /**
     * SVG sengaja tidak diterima: berkasnya disajikan dari origin yang sama
     * dengan aplikasi, dan sebuah SVG bisa membawa <script> di dalamnya.
     */
    private function validateSlot(string $slot): void
    {
        $this->validate(
            [$slot => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:'.self::MAX_KILOBYTES]],
            [],
            ['logo' => 'logo', 'mark' => 'ikon'],
        );
    }

    /**
     * Baris yang ditanyakan ke AppSettingPolicy.
     *
     * Kebijakannya menilai izin, bukan baris tertentu, dan baris logo baru ada
     * setelah unggahan pertama — jadi satu instance (yang tersimpan kalau ada,
     * kalau belum yang kosong) mewakili pertanyaan yang sama untuk kedua slot:
     * boleh atau tidak orang ini mengubah pengaturan aplikasi.
     */
    private function settingRow(AppSettingRepository $settings): AppSetting
    {
        return $settings->findByKey(BrandAssets::keyFor('logo')) ?? new AppSetting;
    }
}
