<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Reservations\Form as ReservationForm;
use App\Livewire\Admin\Reservations\Table as ReservationTable;
use App\Livewire\Admin\TableCategories\Table as TableCategoryTable;
use App\Livewire\Admin\Tables\Form as DiningTableForm;
use App\Livewire\Admin\Tables\StatusBoard;
use App\Livewire\Admin\Tables\Table as DiningTableTable;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Table as DiningTable;
use App\Models\TableCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

/**
 * Reservation, Table dan TableCategory mengikuti pola modul referensi Menu.
 *
 * Dua hal yang khas di modul ini dan karena itu dikunci:
 *  1. "Generate Order" dari reservasi menyentuh DUA model — ia membuat Order dan
 *     mengubah status reservasi. Boleh salah satu saja tidak cukup.
 *  2. Status meja bisa diubah dari dua tempat (dropdown di tabel dan seret-lepas
 *     di papan status), jadi keduanya harus dijaga ability yang sama.
 */
class ReservationTableAuthorizationTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

    private const RESERVATION_READ = ['reservation.viewAny', 'reservation.view'];

    private const RESERVATION_WRITE = [
        'reservation.viewAny', 'reservation.view', 'reservation.create',
        'reservation.update', 'reservation.delete',
    ];

    private const TABLE_READ = ['table.viewAny', 'table.view'];

    private const TABLE_WRITE = ['table.viewAny', 'table.view', 'table.create', 'table.update', 'table.delete'];

    private function table(): DiningTable
    {
        return DiningTable::create([
            'code' => 'T-01',
            'name' => 'Meja Depan',
            'capacity' => 4,
            'status' => 'available',
        ]);
    }

    private function reservation(): Reservation
    {
        return Reservation::create([
            'customer_name' => 'Sari',
            'phone' => '08123',
            'pax' => 2,
            'status' => 'pending',
            'reservation_at' => now()->addDay(),
        ]);
    }

    private function reservationWithItem(): Reservation
    {
        $reservation = $this->reservation();
        $menu = Menu::create(['name' => 'Nasi Goreng', 'slug' => 'nasi-goreng', 'price' => 25000]);

        $reservation->items()->create([
            'menu_id' => $menu->id,
            'menu_name_snapshot' => $menu->name,
            'qty' => 2,
            'unit_price' => 25000,
            'line_total' => 50000,
        ]);

        return $reservation;
    }

    // --- Reservation -------------------------------------------------------

    public function test_pembaca_reservasi_tidak_melihat_tombol_tulis(): void
    {
        $this->reservation();
        $this->actingAsRole('pembaca-reservasi', self::RESERVATION_READ);

        Livewire::test(ReservationTable::class)
            ->assertOk()
            ->assertSee('Sari')
            ->assertDontSee('Tambah Reservasi')
            ->assertDontSee('Hapus');
    }

    public function test_hapus_reservasi_ditolak_saat_aksinya_dipanggil_langsung(): void
    {
        $reservation = $this->reservation();
        $this->actingAsRole('pembaca-reservasi', self::RESERVATION_READ);

        Livewire::test(ReservationTable::class)
            ->call('delete', $reservation->id)
            ->assertForbidden();

        $this->assertModelExists($reservation);
    }

    public function test_generate_order_butuh_izin_kedua_model(): void
    {
        $reservation = $this->reservationWithItem();

        // Kontrol positif: dengan kedua izin, order benar-benar terbentuk.
        $this->actingAsRole('penuh', [...self::RESERVATION_WRITE, 'order.create']);

        Livewire::test(ReservationTable::class)
            ->call('generateOrder', $reservation->id)
            ->assertOk();

        $this->assertSame(1, Order::query()->count());
    }

    public function test_generate_order_ditolak_tanpa_izin_membuat_order(): void
    {
        $reservation = $this->reservationWithItem();

        // Berkuasa penuh atas Reservation, tapi tidak boleh membuat Order.
        $this->actingAsRole('pengelola-reservasi', self::RESERVATION_WRITE);

        Livewire::test(ReservationTable::class)
            ->assertDontSee('data-confirm-title="Generate Order"', escape: false);

        Livewire::test(ReservationTable::class)
            ->call('generateOrder', $reservation->id)
            ->assertForbidden();

        $this->assertSame(0, Order::query()->count());
    }

    public function test_generate_order_ditolak_tanpa_izin_mengubah_reservasi(): void
    {
        $reservation = $this->reservationWithItem();

        // Boleh membuat Order, tapi hanya boleh membaca Reservation — sedangkan
        // aksi ini mengubah status reservasi jadi seated.
        $this->actingAsRole('pembuat-order', [...self::RESERVATION_READ, 'order.create']);

        Livewire::test(ReservationTable::class)
            ->call('generateOrder', $reservation->id)
            ->assertForbidden();

        $this->assertSame(0, Order::query()->count());
    }

    public function test_form_reservasi_edit_ditolak_untuk_yang_hanya_boleh_membuat(): void
    {
        $reservation = $this->reservation();
        $this->actingAsRole('pencatat-reservasi', [...self::RESERVATION_READ, 'reservation.create']);

        Livewire::test(ReservationForm::class)->assertOk();
        Livewire::test(ReservationForm::class, ['reservation' => $reservation])->assertForbidden();
    }

    /**
     * Nama role-nya harus 'cashier' — bukan nama karangan.
     *
     * Grup di routes/admin.php masih dijaga `role:superadmin|admin|cashier`.
     * Role di luar daftar itu ditolak sebelum `can:` sempat dijalankan, jadi
     * test-nya akan hijau tanpa membuktikan apa pun tentang policy. Dengan role
     * yang lolos gerbang kasar itu tapi tanpa permission, yang menolak sudah
     * pasti `can:`.
     */
    public function test_route_reservasi_menolak_tanpa_permission(): void
    {
        $this->actingAsRole('cashier');

        $this->get(route('reservations.index'))->assertForbidden();
        $this->get(route('reservations.create'))->assertForbidden();

        $this->actingAsRole('cashier', self::RESERVATION_READ);

        $this->get(route('reservations.index'))->assertOk();
    }

    // --- Table -------------------------------------------------------------

    public function test_pembaca_meja_tidak_bisa_mengubah_status(): void
    {
        $table = $this->table();
        $this->actingAsRole('pembaca-meja', self::TABLE_READ);

        Livewire::test(DiningTableTable::class)
            ->assertOk()
            ->assertSee('T-01')
            ->assertDontSee('Tambah Meja')
            ->set('statusDrafts.'.$table->id, 'occupied')
            ->call('updateStatus', $table->id)
            ->assertForbidden();

        $this->assertSame('available', $table->fresh()->status);
    }

    public function test_papan_status_dijaga_ability_yang_sama_dengan_tabel(): void
    {
        $table = $this->table();

        // Kontrol positif dulu.
        $this->actingAsRole('pengelola-meja', self::TABLE_WRITE);

        Livewire::test(StatusBoard::class)
            ->call('moveTable', $table->id, 'occupied')
            ->assertOk();

        $this->assertSame('occupied', $table->fresh()->status);

        // Lalu pembaca: seret-lepas tidak boleh jadi celah samping.
        $this->actingAsRole('pembaca-meja', self::TABLE_READ);

        Livewire::test(StatusBoard::class)
            ->call('moveTable', $table->id, 'available')
            ->assertForbidden();

        $this->assertSame('occupied', $table->fresh()->status);
    }

    public function test_hapus_meja_ditolak_saat_aksinya_dipanggil_langsung(): void
    {
        $table = $this->table();
        $this->actingAsRole('pembaca-meja', self::TABLE_READ);

        Livewire::test(DiningTableTable::class)
            ->call('delete', $table->id)
            ->assertForbidden();

        $this->assertModelExists($table);
    }

    public function test_form_meja_edit_ditolak_untuk_yang_hanya_boleh_membuat(): void
    {
        $table = $this->table();
        $this->actingAsRole('pencatat-meja', [...self::TABLE_READ, 'table.create']);

        Livewire::test(DiningTableForm::class)->assertOk();
        Livewire::test(DiningTableForm::class, ['table' => $table])->assertForbidden();
    }

    /** Role 'cashier' dipakai supaya gerbang `role:` bukan yang menolak — lihat catatan di atas. */
    public function test_halaman_qr_menolak_tanpa_permission_view(): void
    {
        $table = $this->table();

        $this->actingAsRole('cashier', self::TABLE_READ);
        $this->get(route('tables.qr', $table))->assertOk();

        $this->actingAsRole('cashier');
        $this->get(route('tables.qr', $table))->assertForbidden();
    }

    // --- TableCategory -----------------------------------------------------

    public function test_kategori_meja_dijaga_terpisah_dari_meja(): void
    {
        TableCategory::create(['name' => 'VIP', 'slug' => 'vip', 'sort_order' => 1, 'is_active' => true]);

        // Berkuasa penuh atas Table, tapi tidak punya apa pun atas TableCategory.
        $this->actingAsRole('pengelola-meja', self::TABLE_WRITE);

        Livewire::test(TableCategoryTable::class)->assertForbidden();

        $this->actingAsRole('pembaca-kategori', ['table_category.viewAny', 'table_category.view']);

        Livewire::test(TableCategoryTable::class)
            ->assertOk()
            ->assertSee('VIP')
            ->assertDontSee('Tambah Kategori');
    }
}
