<?php

namespace Tests\Feature\Table;

use App\Domains\Table\Enums\TableStatus;
use App\Domains\Table\UseCases\ApproveTableSessionUseCase;
use App\Domains\Table\UseCases\CloseTableSessionUseCase;
use App\Domains\Table\UseCases\StartTableSessionUseCase;
use App\Models\Table as DiningTable;
use App\Models\TableSession;
use App\Models\User;
use App\Support\TableSessionContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

/**
 * Halaman penuh — layout, navigasi, @can, dan komponen bersarang — bukan hanya
 * komponennya. Blade gagal diam-diam (CLAUDE.md § H), jadi setiap layar yang
 * disentuh fitur sesi meja dirender utuh sekali di sini.
 */
class TableSessionPagesTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

    private const APPROVER = ['table_session.viewAny', 'table_session.view', 'table_session.update'];

    private DiningTable $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->table = DiningTable::create([
            'code' => 'A3',
            'name' => 'Meja A3',
            'capacity' => 4,
            'status' => TableStatus::Available->value,
        ]);
    }

    private function pending(): TableSession
    {
        return app(StartTableSessionUseCase::class)->handle($this->table, 'Budi', 3);
    }

    private function active(): TableSession
    {
        return app(ApproveTableSessionUseCase::class)->handle($this->pending(), User::factory()->create());
    }

    /** Ikat "HP" test ini ke sesi, seperti sesudah scan QR. */
    private function bind(TableSession $session): void
    {
        $this->withSession([TableSessionContext::KEY => [
            'session_id' => $session->id,
            'table_id' => $this->table->id,
            'table_code' => $this->table->code,
            'qr_token' => $this->table->qr_token,
        ]]);
    }

    public function test_qr_meja_kosong_menampilkan_form_buka_meja(): void
    {
        $this->get(route('checkin.show', ['token' => $this->table->qr_token]))
            ->assertOk()
            ->assertSee('Buka meja ini')
            ->assertSee('Minta buka meja');
    }

    public function test_qr_yang_tidak_dikenal_tidak_membuka_apa_pun(): void
    {
        $this->get(route('checkin.show', ['token' => 'bukan-token-meja']))->assertNotFound();

        $this->assertSame(0, TableSession::query()->count());
    }

    public function test_qr_dengan_sesi_menunggu_menampilkan_kode_gabung(): void
    {
        $session = $this->pending();
        $this->bind($session);

        $this->get(route('checkin.show', ['token' => $this->table->qr_token]))
            ->assertOk()
            ->assertSee('Menunggu konfirmasi kasir')
            ->assertSee($session->join_code);
    }

    public function test_menu_dengan_sesi_menunggu_hanya_menampilkan_banner(): void
    {
        $this->bind($this->pending());

        $this->get(route('public.menu'))
            ->assertOk()
            ->assertSee('menunggu konfirmasi kasir')
            ->assertDontSee('Buka panel meja');
    }

    public function test_menu_dengan_sesi_aktif_menampilkan_panel_dan_kode_gabung(): void
    {
        $session = $this->active();
        $this->bind($session);

        $this->get(route('public.menu'))
            ->assertOk()
            ->assertSee('Buka panel meja')
            ->assertSee('Kode gabung untuk teman satu meja')
            ->assertSee($session->join_code);
    }

    public function test_menu_melupakan_sesi_yang_sudah_berakhir(): void
    {
        $session = $this->active();
        app(CloseTableSessionUseCase::class)->handle($session, User::factory()->create());
        $this->bind($session);

        $this->get(route('public.menu'))
            ->assertOk()
            ->assertDontSee('Buka panel meja')
            ->assertSessionMissing(TableSessionContext::KEY);
    }

    public function test_keranjang_tanpa_sesi_meminta_scan_qr_bukan_memilih_meja(): void
    {
        $this->get(route('public.cart.index'))
            ->assertOk()
            ->assertSee('Scan QR yang ada di meja Anda')
            ->assertDontSee('Pilih Meja');
    }

    public function test_riwayat_sesi_terbuka_untuk_kasir(): void
    {
        $this->pending();
        $this->actingAsRole('cashier', self::APPROVER);

        $this->get(route('table-sessions.index'))
            ->assertOk()
            ->assertSee('Sesi meja')
            ->assertSee('Menunggu konfirmasi meja')
            ->assertSee('Budi');
    }

    public function test_tagihan_pos_menampilkan_tamu_yang_menunggu(): void
    {
        $this->pending();
        $this->actingAsRole('cashier', ['orders.manage', ...self::APPROVER]);

        $this->get(route('pos.bills'))
            ->assertOk()
            ->assertSee('Menunggu konfirmasi meja')
            ->assertSee('Budi');
    }

    /** Baki persetujuan tidak boleh mengunci layar kasir yang belum diberi izinnya. */
    public function test_layar_pos_tetap_terbuka_untuk_kasir_tanpa_izin_sesi(): void
    {
        $this->pending();
        $this->actingAsRole('cashier', ['orders.manage']);

        $this->get(route('pos.bills'))
            ->assertOk()
            ->assertDontSee('Menunggu konfirmasi meja');

        $this->get(route('pos.order.index'))->assertOk();
    }

    public function test_peta_meja_dan_dashboard_resepsionis_menampilkan_tamu_yang_menunggu(): void
    {
        $this->pending();
        $this->actingAsRole('receptionist', ['receptionist.monitor', ...self::APPROVER]);

        $this->get(route('receptionist.table-map'))
            ->assertOk()
            ->assertSee('Budi');

        $this->get(route('receptionist.dashboard'))
            ->assertOk()
            ->assertSee('Menunggu konfirmasi meja');
    }
}
