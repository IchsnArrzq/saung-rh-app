<?php

namespace Tests\Feature\Table;

use App\Domains\Table\Enums\TableSessionCloseReason;
use App\Domains\Table\Enums\TableSessionStatus;
use App\Domains\Table\Enums\TableStatus;
use App\Domains\Table\UseCases\ApproveTableSessionUseCase;
use App\Domains\Table\UseCases\ChangeTableStatusUseCase;
use App\Livewire\Admin\TableSessions\Table as SessionHistory;
use App\Livewire\Frontend\CartCheckout;
use App\Livewire\Frontend\SongRequest;
use App\Livewire\Frontend\TableCheckIn;
use App\Livewire\Staff\TableSessionApprovals;
use App\Models\Menu;
use App\Models\Order;
use App\Models\SongRequest as SongRequestModel;
use App\Models\Table as DiningTable;
use App\Models\TableSession;
use App\Models\User;
use App\Models\VisitorLog;
use App\Support\RestaurantCart;
use App\Support\TableSessionContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

/**
 * Scan QR meja tidak lagi membuka meja sendirian. Sesinya menunggu konfirmasi
 * kasir/resepsionis, HP teman bergabung dengan kode dari HP pertama, dan setiap
 * aksi tamu memeriksa ulang status sesi — jadi foto QR atau HP yang dibawa
 * pulang tidak bisa memesan, chat, atau meminta apa pun ke meja itu.
 */
class TableSessionFlowTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

    private const APPROVER = ['table_session.viewAny', 'table_session.view', 'table_session.update'];

    private function table(string $code = 'A3'): DiningTable
    {
        return DiningTable::create([
            'code' => $code,
            'name' => 'Meja '.$code,
            'capacity' => 4,
            'status' => TableStatus::Available->value,
        ]);
    }

    /** HP pertama di meja meminta meja dibuka. */
    private function requestTable(DiningTable $table, string $name = 'Budi', int $pax = 3): TableSession
    {
        Livewire::test(TableCheckIn::class, ['token' => $table->qr_token])
            ->assertSee('Buka meja ini')
            ->set('customerName', $name)
            ->set('pax', $pax)
            ->call('start')
            ->assertHasNoErrors()
            ->assertSee('Menunggu konfirmasi kasir');

        return TableSession::query()->where('table_id', $table->id)->sole();
    }

    private function approve(TableSession $session): void
    {
        app(ApproveTableSessionUseCase::class)->handle($session, User::factory()->create());
    }

    private function putMenuInCart(): void
    {
        RestaurantCart::addItem(Menu::create(['name' => 'Sate Ayam', 'slug' => 'sate-ayam', 'price' => 20000]), 2);
    }

    private function wrongCode(string $code): string
    {
        return str_pad((string) (((int) $code + 1) % 10000), 4, '0', STR_PAD_LEFT);
    }

    public function test_scan_meja_kosong_hanya_membuat_permintaan_yang_menunggu_kasir(): void
    {
        $table = $this->table();

        $session = $this->requestTable($table);

        $this->assertSame(TableSessionStatus::Pending, $session->status);
        $this->assertSame('Budi', $session->customer_name);
        $this->assertSame(3, $session->pax);
        $this->assertMatchesRegularExpression('/^\d{4}$/', $session->join_code);
        $this->assertSame($session->id, TableSessionContext::sessionId());

        // Belum ada yang mengonfirmasi: meja tetap kosong dan belum dihitung pengunjung.
        $this->assertSame(TableStatus::Available->value, $table->fresh()->status);
        $this->assertSame(0, VisitorLog::query()->count());
    }

    public function test_tamu_yang_belum_disetujui_tidak_bisa_memesan(): void
    {
        $this->requestTable($this->table());
        $this->putMenuInCart();

        Livewire::test(CartCheckout::class)
            ->call('checkout')
            ->assertHasErrors('cart');

        $this->assertSame(0, Order::query()->count());
    }

    public function test_kasir_menyetujui_lalu_meja_terisi_dan_pengunjung_tercatat(): void
    {
        $table = $this->table();
        $session = $this->requestTable($table);

        $cashier = $this->actingAsRole('cashier', self::APPROVER);

        Livewire::test(TableSessionApprovals::class)
            ->assertSee('Budi')
            ->call('approve', $session->id)
            ->assertHasNoErrors();

        $session->refresh();
        $this->assertSame(TableSessionStatus::Active, $session->status);
        $this->assertSame($cashier->id, $session->approved_by);
        $this->assertSame(TableStatus::Occupied->value, $table->fresh()->status);
        $this->assertDatabaseHas('visitor_logs', [
            'table_session_id' => $session->id,
            'pax' => 3,
            'source' => 'qr',
        ]);
    }

    public function test_pesanan_selalu_masuk_ke_meja_milik_sesi(): void
    {
        $table = $this->table('A3');
        $other = $this->table('B7');
        $session = $this->requestTable($table);
        $this->approve($session);

        // Tautan lama `?table_id=` tidak lagi bisa memindahkan pesanan ke meja lain.
        $context = RestaurantCart::syncContextFromRequest(Request::create('/menu', 'GET', ['table_id' => $other->id]));
        $this->assertSame($table->id, $context['table_id']);

        $this->putMenuInCart();

        Livewire::test(CartCheckout::class)
            ->assertSet('customerName', 'Budi')
            ->call('checkout')
            ->assertHasNoErrors()
            ->assertRedirect(route('public.menu'));

        $order = Order::query()->sole();
        $this->assertSame($table->id, $order->table_id);
        $this->assertSame($session->id, $order->table_session_id);
    }

    public function test_hp_teman_bergabung_dengan_kode_dari_hp_pertama(): void
    {
        $table = $this->table();
        $session = $this->requestTable($table);

        // HP kedua: belum terikat ke sesi mana pun.
        TableSessionContext::clear();

        Livewire::test(TableCheckIn::class, ['token' => $table->qr_token])
            ->assertSee('Gabung ke meja ini')
            ->assertDontSee('Buka meja ini')
            ->set('joinCode', $this->wrongCode($session->join_code))
            ->call('join')
            ->assertHasErrors('joinCode')
            ->set('joinCode', $session->join_code)
            ->call('join')
            ->assertHasNoErrors()
            ->assertSee('Menunggu konfirmasi kasir');

        $this->assertSame($session->id, TableSessionContext::sessionId());
        $this->assertSame(1, TableSession::query()->count());
    }

    public function test_kode_gabung_dibatasi_lima_percobaan_salah(): void
    {
        $table = $this->table();
        $session = $this->requestTable($table);
        TableSessionContext::clear();

        $page = Livewire::test(TableCheckIn::class, ['token' => $table->qr_token]);

        for ($i = 0; $i < 5; $i++) {
            $page->set('joinCode', $this->wrongCode($session->join_code))->call('join');
        }

        // Kode yang benar pun ditahan sampai jedanya habis.
        $page->set('joinCode', $session->join_code)
            ->call('join')
            ->assertHasErrors('joinCode')
            ->assertSee('Terlalu banyak kode yang salah');

        $this->assertNull(TableSessionContext::sessionId());
    }

    public function test_hp_yang_sudah_disetujui_langsung_ke_menu_saat_scan_ulang(): void
    {
        $table = $this->table();
        $this->approve($this->requestTable($table));

        Livewire::test(TableCheckIn::class, ['token' => $table->qr_token])
            ->assertRedirect(route('public.menu'));
    }

    public function test_meja_dikosongkan_staf_menutup_sesi_dan_mengunci_panel_meja(): void
    {
        $table = $this->table();
        $session = $this->requestTable($table);
        $this->approve($session);

        app(ChangeTableStatusUseCase::class)->handle($table->fresh(), TableStatus::Cleaning);

        $session->refresh();
        $this->assertSame(TableSessionStatus::Closed, $session->status);
        $this->assertSame(TableSessionCloseReason::TableReleased, $session->close_reason);

        // HP yang masih memegang sesi itu — misalnya dibawa pulang — tidak bisa
        // lagi mengirim apa pun ke meja.
        Livewire::test(SongRequest::class)
            ->set('title', 'Bengawan Solo')
            ->call('submit')
            ->assertHasErrors('title');

        $this->assertSame(0, SongRequestModel::query()->count());
    }

    public function test_staf_menonaktifkan_sesi_dari_riwayat_lalu_pesanan_ditolak(): void
    {
        $session = $this->requestTable($this->table());
        $this->approve($session);

        $cashier = $this->actingAsRole('cashier', self::APPROVER);

        Livewire::test(SessionHistory::class)
            ->assertSee('Budi')
            ->call('close', $session->id)
            ->assertHasNoErrors();

        $session->refresh();
        $this->assertSame(TableSessionStatus::Closed, $session->status);
        $this->assertSame(TableSessionCloseReason::Deactivated, $session->close_reason);
        $this->assertSame($cashier->id, $session->closed_by);

        $this->putMenuInCart();

        Livewire::test(CartCheckout::class)
            ->call('checkout')
            ->assertHasErrors('cart');

        $this->assertSame(0, Order::query()->count());
    }

    public function test_peran_tanpa_izin_ubah_tidak_bisa_menyetujui(): void
    {
        $session = $this->requestTable($this->table());

        // Pelayan boleh melihat sesi meja, tapi tidak memutuskannya.
        $this->actingAsRole('waiter', ['table_session.viewAny', 'table_session.view']);

        Livewire::test(TableSessionApprovals::class)
            ->assertSee('Budi')
            ->assertDontSeeHtml("approve('")
            ->call('approve', $session->id)
            ->assertForbidden();

        $this->assertSame(TableSessionStatus::Pending, $session->fresh()->status);
    }

    public function test_tamu_diberi_tahu_saat_permintaannya_ditolak(): void
    {
        $table = $this->table();
        $session = $this->requestTable($table);

        $this->actingAsRole('receptionist', self::APPROVER);

        Livewire::test(TableSessionApprovals::class)
            ->call('reject', $session->id)
            ->assertHasNoErrors();

        $this->assertSame(TableSessionStatus::Rejected, $session->fresh()->status);

        Livewire::test(TableCheckIn::class, ['token' => $table->qr_token])
            ->assertSee('ditolak kasir')
            ->assertSee('Buka meja ini');
    }

    public function test_satu_scan_tidak_bisa_diputuskan_dua_kali(): void
    {
        $session = $this->requestTable($this->table());
        $this->actingAsRole('cashier', self::APPROVER);

        Livewire::test(TableSessionApprovals::class)
            ->call('approve', $session->id)
            ->call('reject', $session->id)
            ->assertHasErrors('session');

        $this->assertSame(TableSessionStatus::Active, $session->fresh()->status);
    }
}
