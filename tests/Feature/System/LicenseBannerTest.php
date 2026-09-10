<?php

namespace Tests\Feature\System;

use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

/**
 * Peringatan lisensi di kerangka portal admin.
 *
 * Sebelumnya keadaan lisensi hanya terlihat kalau seseorang sengaja membuka
 * halaman Lisensi — yang berarti tidak pernah terlihat sampai terlambat.
 *
 * Ini pemberitahuan, bukan pagar: tidak ada yang dikunci saat lisensi habis.
 */
class LicenseBannerTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

    private function license(string $status, ?string $expiresAt): Subscription
    {
        return Subscription::query()->create([
            'plan' => 'professional',
            'license_key' => 'CR-'.uniqid(),
            'status' => $status,
            'seats' => 10,
            'started_at' => now()->subYear(),
            'expires_at' => $expiresAt,
        ]);
    }

    private function banner(): string
    {
        return Blade::render('<x-license-banner />');
    }

    public function test_lisensi_kedaluwarsa_memunculkan_peringatan(): void
    {
        $this->actingAsRole('admin', ['subscription.viewAny']);
        $this->license('active', now()->subDay()->toDateTimeString());

        $html = $this->banner();

        $this->assertStringContainsString('Lisensi kedaluwarsa', $html);
        $this->assertStringContainsString(route('system.license'), $html);
    }

    public function test_lisensi_yang_hampir_habis_memunculkan_peringatan(): void
    {
        $this->actingAsRole('admin', ['subscription.viewAny']);
        $this->license('active', now()->addDays(3)->toDateTimeString());

        $this->assertStringContainsString('Lisensi berakhir dalam', $this->banner());
    }

    public function test_lisensi_yang_masih_panjang_tidak_mengganggu(): void
    {
        $this->actingAsRole('admin', ['subscription.viewAny']);
        $this->license('active', now()->addMonths(6)->toDateTimeString());

        $this->assertSame('', trim($this->banner()));
    }

    /**
     * Pemasangan baru belum punya baris lisensi sama sekali. Banner sejak hari
     * pertama akan diabaikan sebelum sempat berarti apa-apa.
     */
    public function test_tanpa_baris_lisensi_tidak_ada_banner(): void
    {
        $this->actingAsRole('admin', ['subscription.viewAny']);

        $this->assertSame('', trim($this->banner()));
    }

    /**
     * Kasir dan resepsionis tidak mengurus lisensi — buat mereka ini kebisingan,
     * dan query-nya pun tidak perlu jalan.
     */
    public function test_peran_tanpa_izin_lisensi_tidak_melihat_apa_pun(): void
    {
        $this->actingAsRole('cashier', ['orders.manage']);
        $this->license('active', now()->subDay()->toDateTimeString());

        $this->assertSame('', trim($this->banner()));
    }
}
