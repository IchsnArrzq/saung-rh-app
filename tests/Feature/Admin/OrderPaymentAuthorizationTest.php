<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Orders\Form as OrderForm;
use App\Livewire\Admin\Orders\Table as OrderTable;
use App\Livewire\Admin\Payments\Form as PaymentForm;
use App\Livewire\Admin\Payments\Table as PaymentTable;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

/**
 * Order dan Payment mengikuti pola modul referensi Menu: route, komponen
 * Livewire dan tombol Blade menanyakan Policy yang sama.
 *
 * Selain tiga lapis itu, ada satu hal yang khas di sini: tombol "Payment" di
 * tabel order membuat baris Payment, jadi yang menjaganya PaymentPolicy — bukan
 * OrderPolicy. Gampang tertukar, jadi dikunci di test.
 */
class OrderPaymentAuthorizationTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

    private const ORDER_READ = ['order.viewAny', 'order.view'];

    private const ORDER_WRITE = ['order.viewAny', 'order.view', 'order.create', 'order.update', 'order.delete'];

    private const PAYMENT_READ = ['payment.viewAny', 'payment.view'];

    private const PAYMENT_WRITE = ['payment.viewAny', 'payment.view', 'payment.create', 'payment.update', 'payment.delete'];

    private function order(): Order
    {
        return Order::create([
            'order_number' => 'ORD-TEST-1',
            'customer_name' => 'Budi',
            'status' => 'draft',
            'subtotal' => 50000,
            'discount' => 0,
            'tax' => 0,
            'total' => 50000,
            'ordered_at' => now(),
        ]);
    }

    private function payment(Order $order): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'method' => 'cash',
            'type' => 'full',
            'status' => 'paid',
            'amount' => 50000,
            'paid_at' => now(),
        ]);
    }

    public function test_pembaca_order_tidak_melihat_tombol_tulis(): void
    {
        $this->order();
        $this->actingAsRole('pembaca-order', self::ORDER_READ);

        Livewire::test(OrderTable::class)
            ->assertOk()
            ->assertSee('ORD-TEST-1')
            ->assertDontSee('Buat Order')
            ->assertDontSee('Hapus');
    }

    public function test_pengelola_order_melihat_tombol_tulis(): void
    {
        $this->order();
        $this->actingAsRole('pengelola-order', self::ORDER_WRITE);

        Livewire::test(OrderTable::class)
            ->assertSee('Buat Order')
            ->assertSee('Hapus');
    }

    public function test_hapus_order_ditolak_saat_aksinya_dipanggil_langsung(): void
    {
        $order = $this->order();
        $this->actingAsRole('pembaca-order', self::ORDER_READ);

        Livewire::test(OrderTable::class)
            ->call('delete', $order->id)
            ->assertForbidden();

        $this->assertModelExists($order);
    }

    public function test_tombol_payment_dijaga_payment_policy_bukan_order_policy(): void
    {
        $order = $this->order();

        // Kontrol positif lebih dulu: tanpa ini, assertDontSee di bawah bisa
        // lolos hanya karena penandanya memang tidak pernah dirender.
        $this->actingAsRole('kasir-uji', [...self::ORDER_WRITE, ...self::PAYMENT_WRITE]);

        Livewire::test(OrderTable::class)
            ->assertSee('data-confirm-title="Buat Payment"', escape: false);

        // Berkuasa penuh atas Order, tapi tidak boleh membuat Payment.
        $this->actingAsRole('pengelola-order', self::ORDER_WRITE);

        Livewire::test(OrderTable::class)
            ->assertSee('Buat Order')
            ->assertDontSee('data-confirm-title="Buat Payment"', escape: false);

        Livewire::test(OrderTable::class)
            ->call('createPayment', $order->id)
            ->assertForbidden();

        $this->assertSame(0, Payment::query()->count());
    }

    public function test_detail_order_ditolak_tanpa_permission_view(): void
    {
        $order = $this->order();
        $this->actingAsRole('tanpa-akses');

        Livewire::test(OrderTable::class)
            ->assertForbidden();

        $this->actingAsRole('daftar-saja', ['order.viewAny']);

        Livewire::test(OrderTable::class)
            ->call('showDetail', $order->id)
            ->assertForbidden();
    }

    public function test_form_order_edit_ditolak_untuk_yang_hanya_boleh_membuat(): void
    {
        $order = $this->order();
        $this->actingAsRole('pencatat-order', ['order.viewAny', 'order.view', 'order.create']);

        Livewire::test(OrderForm::class)->assertOk();
        Livewire::test(OrderForm::class, ['order' => $order])->assertForbidden();
    }

    public function test_route_order_menolak_tanpa_permission(): void
    {
        $this->actingAsRole('tanpa-akses');

        $this->get(route('orders.index'))->assertForbidden();
        $this->get(route('orders.create'))->assertForbidden();
    }

    public function test_pembaca_payment_tidak_melihat_tombol_tulis(): void
    {
        $this->payment($this->order());
        $this->actingAsRole('pembaca-payment', self::PAYMENT_READ);

        Livewire::test(PaymentTable::class)
            ->assertOk()
            ->assertDontSee('Tambah Pembayaran')
            ->assertDontSee('Hapus');
    }

    public function test_hapus_payment_ditolak_saat_aksinya_dipanggil_langsung(): void
    {
        $payment = $this->payment($this->order());
        $this->actingAsRole('pembaca-payment', self::PAYMENT_READ);

        Livewire::test(PaymentTable::class)
            ->call('delete', $payment->id)
            ->assertForbidden();

        $this->assertModelExists($payment);
    }

    public function test_pengelola_payment_boleh_menghapus(): void
    {
        $payment = $this->payment($this->order());
        $this->actingAsRole('pengelola-payment', self::PAYMENT_WRITE);

        Livewire::test(PaymentTable::class)
            ->assertSee('Tambah Pembayaran')
            ->call('delete', $payment->id)
            ->assertOk();

        $this->assertModelMissing($payment);
    }

    public function test_form_payment_edit_ditolak_untuk_yang_hanya_boleh_membuat(): void
    {
        $payment = $this->payment($this->order());
        $this->actingAsRole('pencatat-payment', ['payment.viewAny', 'payment.view', 'payment.create']);

        Livewire::test(PaymentForm::class)->assertOk();
        Livewire::test(PaymentForm::class, ['payment' => $payment])->assertForbidden();
    }

    public function test_route_payment_menolak_tanpa_permission(): void
    {
        $this->actingAsRole('tanpa-akses');

        $this->get(route('payments.index'))->assertForbidden();
        $this->get(route('payments.create'))->assertForbidden();
    }
}
