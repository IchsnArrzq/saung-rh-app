<?php

namespace Tests\Feature\Pos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

/**
 * Kasir hanya punya dua layar: transaksi dan tagihan meja. Sisa rute dari
 * Route::resource dihapus karena halamannya tidak pernah dibangun, jadi test
 * ini menjaga dua yang tersisa tetap terbuka dan yang mati tetap mati.
 */
class CashierRoutesTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAsRole('cashier', ['orders.manage']);
    }

    public function test_kasir_bisa_membuka_layar_transaksi(): void
    {
        $this->get(route('pos.order.index'))->assertOk();
    }

    public function test_kasir_bisa_membuka_tagihan_meja(): void
    {
        $this->get(route('pos.bills'))->assertOk();
    }

    /**
     * Route::resource dulu mendaftarkan enam rute lain yang halamannya tidak
     * pernah ada. Menghidupkannya kembali tanpa halaman berarti tombol atau
     * tautan bisa menunjuk ke layar kosong lagi.
     */
    public function test_rute_pos_tanpa_halaman_tidak_dihidupkan_lagi(): void
    {
        $routes = app('router')->getRoutes();

        foreach (['create', 'store', 'show', 'edit', 'update', 'destroy'] as $action) {
            $this->assertNull(
                $routes->getByName("pos.order.{$action}"),
                "Rute pos.order.{$action} terdaftar lagi tanpa halaman."
            );
        }
    }
}
