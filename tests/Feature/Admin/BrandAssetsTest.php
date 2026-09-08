<?php

namespace Tests\Feature\Admin;

use App\Domains\System\Services\BusinessProfile;
use App\Livewire\Admin\System\AppSettingsManager;
use App\Livewire\Admin\System\BrandAssetsManager;
use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

/**
 * Logo dan ikon aplikasi berasal dari Pengaturan Aplikasi, bukan dari berkas
 * yang ditulis di layout.
 */
class BrandAssetsTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

    private function profile(): BusinessProfile
    {
        return app(BusinessProfile::class);
    }

    public function test_tanpa_unggahan_memakai_berkas_bawaan(): void
    {
        $this->assertFalse($this->profile()->hasCustomLogo());
        $this->assertStringContainsString('assets/logo-cr-cafe-resto.png', $this->profile()->logoUrl());
    }

    public function test_logo_yang_diunggah_dipakai_di_seluruh_aplikasi(): void
    {
        Storage::fake('public');
        $this->actingAsSuperadmin();

        Livewire::test(BrandAssetsManager::class)
            ->set('logo', UploadedFile::fake()->image('logo-warung.png'))
            ->call('saveLogo')
            ->assertHasNoErrors();

        $path = AppSetting::query()->where('key', BusinessProfile::LOGO_KEY)->value('value');

        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);

        $this->assertTrue($this->profile()->hasCustomLogo());
        $this->assertStringContainsString($path, $this->profile()->logoUrl());

        // Halaman publik memakai wordmark-nya lewat <x-brand-logo>.
        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee($this->profile()->logoUrl(), escape: false);
    }

    public function test_mengganti_logo_membuang_berkas_lama(): void
    {
        Storage::fake('public');
        $this->actingAsSuperadmin();

        Livewire::test(BrandAssetsManager::class)
            ->set('logo', UploadedFile::fake()->image('lama.png'))
            ->call('saveLogo');

        $lama = AppSetting::query()->where('key', BusinessProfile::LOGO_KEY)->value('value');

        Livewire::test(BrandAssetsManager::class)
            ->set('logo', UploadedFile::fake()->image('baru.png'))
            ->call('saveLogo');

        $baru = AppSetting::query()->where('key', BusinessProfile::LOGO_KEY)->value('value');

        $this->assertNotSame($lama, $baru);
        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists($baru);
    }

    public function test_hapus_logo_kembali_ke_bawaan(): void
    {
        Storage::fake('public');
        $this->actingAsSuperadmin();

        $component = Livewire::test(BrandAssetsManager::class)
            ->set('logo', UploadedFile::fake()->image('logo.png'))
            ->call('saveLogo');

        $path = AppSetting::query()->where('key', BusinessProfile::LOGO_KEY)->value('value');

        $component->call('removeLogo');

        Storage::disk('public')->assertMissing($path);
        $this->assertFalse($this->profile()->hasCustomLogo());
        $this->assertStringContainsString('assets/logo-cr-cafe-resto.png', $this->profile()->logoUrl());
    }

    public function test_berkas_yang_hilang_jatuh_ke_bawaan_bukan_gambar_rusak(): void
    {
        Storage::fake('public');

        AppSetting::query()->create([
            'key' => BusinessProfile::LOGO_KEY,
            'value' => 'brand/sudah-tidak-ada.png',
            'group' => 'brand',
            'type' => 'image',
        ]);

        $this->assertFalse($this->profile()->hasCustomLogo());
        $this->assertStringContainsString('assets/logo-cr-cafe-resto.png', $this->profile()->logoUrl());
    }

    public function test_menyimpan_pengaturan_teks_tidak_menimpa_logo(): void
    {
        Storage::fake('public');
        $this->actingAsSuperadmin();

        AppSetting::query()->create(['key' => 'app.name', 'value' => 'CR Cafe', 'group' => 'profile']);

        Livewire::test(BrandAssetsManager::class)
            ->set('logo', UploadedFile::fake()->image('logo.png'))
            ->call('saveLogo');

        $path = AppSetting::query()->where('key', BusinessProfile::LOGO_KEY)->value('value');

        // Form teks tidak boleh mengenal baris berkas: kalau ikut ter-bind, nilai
        // lamanya akan ditulis balik dan logo di seluruh aplikasi hilang.
        Livewire::test(AppSettingsManager::class)
            ->assertSetStrict('values.brand.logo', null)
            ->set('values.app.name', 'Warung Bu Tini')
            ->call('save');

        $this->assertSame('Warung Bu Tini', AppSetting::query()->where('key', 'app.name')->value('value'));
        $this->assertSame($path, AppSetting::query()->where('key', BusinessProfile::LOGO_KEY)->value('value'));
    }

    public function test_manifest_memakai_ikon_yang_diunggah(): void
    {
        Storage::fake('public');
        $this->actingAsSuperadmin();

        Livewire::test(BrandAssetsManager::class)
            ->set('mark', UploadedFile::fake()->image('ikon.png', 512, 512))
            ->call('saveMark');

        $this->get(route('pwa.manifest'))
            ->assertOk()
            ->assertJsonFragment(['src' => $this->profile()->markUrl(), 'sizes' => '512x512']);
    }

    public function test_unggahan_ditolak_tanpa_izin_tulis(): void
    {
        Storage::fake('public');
        $this->actingAsRole('pembaca', ['app_setting.viewAny', 'app_setting.view']);

        // Livewire menangkap AuthorizationException dan menerjemahkannya jadi 403.
        Livewire::test(BrandAssetsManager::class)
            ->set('logo', UploadedFile::fake()->image('logo.png'))
            ->call('saveLogo')
            ->assertForbidden();

        $this->assertFalse($this->profile()->hasCustomLogo());
    }

    public function test_berkas_bukan_gambar_ditolak(): void
    {
        Storage::fake('public');
        $this->actingAsSuperadmin();

        Livewire::test(BrandAssetsManager::class)
            ->set('logo', UploadedFile::fake()->create('menu.pdf', 100, 'application/pdf'))
            ->call('saveLogo')
            ->assertHasErrors(['logo']);

        $this->assertFalse($this->profile()->hasCustomLogo());
    }
}
