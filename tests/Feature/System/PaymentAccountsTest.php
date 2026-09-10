<?php

namespace Tests\Feature\System;

use App\Domains\Payment\Enums\PaymentMethod;
use App\Domains\System\QueryUseCases\GetPaymentAccountsQueryUseCase;
use App\Livewire\Pos\TableBills;
use App\Models\PaymentAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

/**
 * Rekening tujuan yang dibacakan ke tamu saat metode non-tunai dipilih.
 *
 * Sebelumnya `payment_accounts` hanya punya layar CRUD admin — nomornya tidak
 * pernah sampai ke kasir maupun resepsionis yang harus menyebutkannya.
 */
class PaymentAccountsTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

    private function query(): GetPaymentAccountsQueryUseCase
    {
        return app(GetPaymentAccountsQueryUseCase::class);
    }

    private function account(array $attributes = []): PaymentAccount
    {
        return PaymentAccount::query()->create(array_merge([
            'label' => 'BCA Utama',
            'type' => 'bank',
            'provider' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'PT CR Cafe Resto',
            'is_active' => true,
            'sort_order' => 1,
        ], $attributes));
    }

    public function test_transfer_hanya_mengambil_rekening_bank_yang_aktif(): void
    {
        $this->account(['label' => 'BCA Utama', 'sort_order' => 2]);
        $this->account(['label' => 'Mandiri', 'sort_order' => 1]);
        $this->account(['label' => 'BNI Lama', 'is_active' => false, 'sort_order' => 0]);
        $this->account(['label' => 'QRIS Kasir', 'type' => 'qris', 'sort_order' => 0]);

        $accounts = $this->query()->forMethod(PaymentMethod::Transfer);

        // Urutannya mengikuti sort_order yang diatur admin, bukan urutan input.
        $this->assertSame(['Mandiri', 'BCA Utama'], $accounts->pluck('label')->all());
    }

    public function test_qris_dan_ewallet_mengambil_tipenya_sendiri(): void
    {
        $this->account(['label' => 'QRIS Kasir', 'type' => 'qris']);
        $this->account(['label' => 'GoPay', 'type' => 'ewallet']);
        $this->account(['label' => 'BCA Utama', 'type' => 'bank']);

        $this->assertSame(['QRIS Kasir'], $this->query()->forMethod(PaymentMethod::Qris)->pluck('label')->all());
        $this->assertSame(['GoPay'], $this->query()->forMethod(PaymentMethod::Ewallet)->pluck('label')->all());
    }

    /**
     * Tunai dibayar di meja kasir dan kartu lewat mesin EDC, jadi tidak ada
     * rekening yang perlu disebutkan — dan tidak ada peringatan "belum diatur"
     * yang perlu muncul.
     */
    public function test_metode_tanpa_rekening_tujuan_tidak_meminta_apa_pun(): void
    {
        $this->account();

        foreach ([PaymentMethod::Cash, PaymentMethod::DebitCard, PaymentMethod::CreditCard] as $method) {
            $this->assertFalse($this->query()->expectsAccount($method), $method->value);
            $this->assertTrue($this->query()->forMethod($method)->isEmpty(), $method->value);
        }

        $this->assertTrue($this->query()->expectsAccount(PaymentMethod::Transfer));
    }

    public function test_nomor_rekening_dirender_untuk_dibacakan(): void
    {
        $accounts = collect([$this->account()]);

        $html = Blade::render(
            '<x-payment-accounts :accounts="$accounts" :expected="true" />',
            ['accounts' => $accounts],
        );

        $this->assertStringContainsString('1234567890', $html);
        $this->assertStringContainsString('PT CR Cafe Resto', $html);
    }

    /**
     * "Belum diatur" dan "memang tidak perlu" tampak sama kalau keduanya
     * merender daftar kosong — yang pertama harus terbaca sebagai peringatan.
     */
    public function test_metode_yang_butuh_rekening_tapi_belum_diisi_memberi_peringatan(): void
    {
        $empty = collect();

        $warned = Blade::render(
            '<x-payment-accounts :accounts="$accounts" :expected="true" />',
            ['accounts' => $empty],
        );
        $silent = Blade::render(
            '<x-payment-accounts :accounts="$accounts" :expected="false" />',
            ['accounts' => $empty],
        );

        $this->assertStringContainsString('Belum ada rekening aktif', $warned);
        $this->assertSame('', trim($silent));
    }

    public function test_layar_tagihan_kasir_membawa_rekening_ke_view(): void
    {
        $this->actingAsRole('cashier', ['orders.manage']);

        Livewire::test(TableBills::class)
            ->assertOk()
            // Tidak ada tagihan yang dibuka, jadi belum ada rekening yang dibaca.
            ->assertViewHas('accountExpected', false)
            ->assertViewHas('accounts', fn ($accounts) => $accounts->isEmpty());
    }
}
