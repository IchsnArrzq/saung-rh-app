<?php

namespace Tests\Feature\Pos;

use App\Livewire\Pos\TableBills;
use App\Livewire\Staff\Receptionist\BookingBoard;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

/**
 * Method tulis di dua layar keuangan ini diadili sendiri, bukan menumpang
 * middleware rutenya.
 *
 * Livewire memanggil method komponen lewat POST /livewire/update — request itu
 * tidak melewati `can:orders.manage` di routes/pos.php maupun
 * `can:reservations.manage` di routes/staff.php. Tanpa authorize() di dalam
 * method, siapa pun yang sudah login bisa melunasi tagihan atau mencatat DP,
 * dan @can di Blade hanya menyembunyikan tombolnya.
 */
class LivewireWriteGuardTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

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

    public function test_peran_tanpa_izin_pembayaran_tidak_bisa_melunasi_tagihan(): void
    {
        // Waiter memang punya akses ke Order, tapi tidak ke Payment.
        $this->actingAsRole('waiter', ['order.viewAny', 'order.view', 'order.update']);

        Livewire::test(TableBills::class)
            ->call('settle')
            ->assertForbidden();
    }

    public function test_kasir_dengan_izin_pembayaran_lolos_gerbangnya(): void
    {
        $this->actingAsRole('cashier', ['payment.create']);

        // Tidak ada tagihan yang dipilih, jadi yang diharapkan bukan pelunasan
        // berhasil — hanya bahwa ia lolos Policy dan berhenti di pesan yang
        // bisa dibaca, bukan di 403.
        Livewire::test(TableBills::class)
            ->call('settle')
            ->assertOk()
            ->assertHasErrors('settle');
    }

    public function test_peran_tanpa_izin_reservasi_tidak_bisa_menyentuh_papan_booking(): void
    {
        $reservation = $this->reservation();
        $this->actingAsRole('waiter', ['order.viewAny']);

        Livewire::test(BookingBoard::class)
            ->call('openDeposit', $reservation->id)
            ->assertForbidden();

        Livewire::test(BookingBoard::class)
            ->call('setStatus', $reservation->id, 'confirmed')
            ->assertForbidden();

        // Reservation.status masih kolom string biasa, belum di-cast ke Enum.
        $this->assertSame('pending', $reservation->fresh()->status);
    }

    public function test_resepsionis_dengan_izin_reservasi_boleh_membuka_form_dp(): void
    {
        $reservation = $this->reservation();
        $this->actingAsRole('receptionist', ['reservation.viewAny', 'reservation.view', 'reservation.update']);

        Livewire::test(BookingBoard::class)
            ->call('openDeposit', $reservation->id)
            ->assertOk()
            ->assertSet('depositFor', $reservation->id);
    }
}
