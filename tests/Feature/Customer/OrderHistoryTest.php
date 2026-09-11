<?php

namespace Tests\Feature\Customer;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

/**
 * Riwayat pesanan pelanggan: dulu pesanan yang dibuat dari portal pelanggan
 * hilang dari pandangan begitu keranjangnya dikirim — dashboard hanya memuat
 * reservasi. Test ini menjaga dua janji halaman barunya: yang tampil hanya
 * pesanan milik akun itu sendiri, dan pesanan yang masih berjalan muncul
 * paling atas di dashboard.
 */
class OrderHistoryTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

    private function orderFor(User $customer, string $number, string $status, ?Carbon $at = null): Order
    {
        $order = Order::create([
            'order_number' => $number,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'status' => $status,
            'subtotal' => 30000,
            'discount' => 0,
            'tax' => 0,
            'total' => 30000,
            'ordered_at' => $at ?? now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_name_snapshot' => 'Nasi Goreng',
            'qty' => 2,
            'price' => 15000,
            'line_total' => 30000,
        ]);

        return $order;
    }

    public function test_pelanggan_hanya_melihat_pesanannya_sendiri(): void
    {
        $me = $this->actingAsRole('customer');
        $someoneElse = User::factory()->create();

        $this->orderFor($me, 'ORD-MILIKKU', 'paid');
        $this->orderFor($someoneElse, 'ORD-ORANG-LAIN', 'paid');

        $this->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('ORD-MILIKKU')
            ->assertDontSee('ORD-ORANG-LAIN');
    }

    public function test_riwayat_urut_terbaru_dulu(): void
    {
        $me = $this->actingAsRole('customer');

        $this->orderFor($me, 'ORD-KEMARIN', 'paid', now()->subDay());
        $this->orderFor($me, 'ORD-HARI-INI', 'served', now());

        $this->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSeeInOrder(['ORD-HARI-INI', 'ORD-KEMARIN']);
    }

    public function test_riwayat_kosong_menawarkan_jalan_untuk_mulai_memesan(): void
    {
        $this->actingAsRole('customer');

        $this->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('Belum ada pesanan')
            ->assertSee(route('customer.menus.tables'), escape: false);
    }

    public function test_dashboard_menampilkan_hanya_pesanan_yang_masih_berjalan(): void
    {
        $me = $this->actingAsRole('customer');

        $this->orderFor($me, 'ORD-DIMASAK', 'preparing');
        $this->orderFor($me, 'ORD-SUDAH-LUNAS', 'paid');

        $this->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Pesanan berjalan')
            ->assertSee('ORD-DIMASAK')
            ->assertDontSee('ORD-SUDAH-LUNAS');
    }

    /**
     * Order yang lupa ditutup lantai tetap berstatus non-final selamanya.
     * Dashboard tidak boleh menyajikannya sebagai sesuatu yang sedang terjadi,
     * tapi riwayat tetap mencatatnya dengan status aslinya.
     */
    public function test_pesanan_macet_dari_kemarin_tidak_disebut_berjalan(): void
    {
        $me = $this->actingAsRole('customer');

        $this->orderFor($me, 'ORD-MACET-KEMARIN', 'preparing', now()->subDay());

        $this->get(route('customer.dashboard'))
            ->assertOk()
            ->assertDontSee('Pesanan berjalan')
            ->assertDontSee('ORD-MACET-KEMARIN');

        $this->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('ORD-MACET-KEMARIN');
    }

    public function test_dashboard_tanpa_pesanan_berjalan_tidak_merender_bloknya(): void
    {
        $me = $this->actingAsRole('customer');

        $this->orderFor($me, 'ORD-SUDAH-LUNAS', 'paid');

        $this->get(route('customer.dashboard'))
            ->assertOk()
            ->assertDontSee('Pesanan berjalan');
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('customer.orders.index'))
            ->assertRedirect(route('login'));
    }
}
