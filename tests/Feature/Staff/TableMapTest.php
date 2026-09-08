<?php

namespace Tests\Feature\Staff;

use App\Livewire\Staff\Receptionist\TableMap;
use App\Models\Table as DiningTable;
use App\Support\Quantity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Peta meja menempatkan tile secara absolut. Meja yang menyimpan koordinat dan
 * meja yang tidak (jatuh ke tata letak berurutan) dulu bisa merebut sel yang
 * sama, dan tile-nya bertumpuk — persis yang terjadi pada meja demo reservasi.
 */
class TableMapTest extends TestCase
{
    use RefreshDatabase;

    private function table(string $code, string $status = 'available', ?int $x = null, ?int $y = null): DiningTable
    {
        return DiningTable::create([
            'code' => $code,
            'name' => 'Meja '.$code,
            'capacity' => 4,
            'status' => $status,
            'position_x' => $x,
            'position_y' => $y,
        ]);
    }

    public function test_meja_tanpa_koordinat_tidak_menempati_sel_yang_sudah_dipakai(): void
    {
        // Kode "RSV-DEMO" berada sebelum "T-01" saat diurut, jadi tata letak
        // berurutan akan menawarkan sel (0,0) — yang sudah dimiliki T-01.
        $this->table('RSV-DEMO', 'reserved');
        $this->table('T-01', 'available', 0, 0);
        $this->table('T-02', 'cleaning', 1, 0);

        $html = Livewire::test(TableMap::class)->assertOk()->html();

        preg_match_all('/left: (\d+)px; top: (\d+)px/', $html, $matches, PREG_SET_ORDER);

        $cells = array_map(fn (array $m): string => $m[1].':'.$m[2], $matches);

        $this->assertCount(3, $cells, 'Setiap meja harus punya satu tile.');
        $this->assertSame($cells, array_values(array_unique($cells)), 'Ada tile yang bertumpuk di sel yang sama.');
    }

    public function test_ringkasan_status_memakai_label_bukan_nilai_backing_enum(): void
    {
        $this->table('T-01', 'order_in', 0, 0);
        $this->table('T-02', 'order_in', 1, 0);
        $this->table('T-03', 'cleaning', 2, 0);

        Livewire::test(TableMap::class)
            ->assertOk()
            ->assertSee('Pesanan Masuk')
            ->assertSee('Perlu Dibersihkan')
            ->assertDontSee('order_in')
            ->assertDontSee('unknown');
    }

    #[DataProvider('quantities')]
    public function test_kuantitas_stok_tidak_dibaca_sebagai_ribuan(string $raw, string $screen, string $input): void
    {
        $this->assertSame($screen, Quantity::format($raw));
        $this->assertSame($input, Quantity::input($raw));
    }

    /** @return array<string, array{string, string, string}> */
    public static function quantities(): array
    {
        return [
            'bulat dari decimal(10,3)' => ['15.000', '15', '15'],
            'setengah' => ['0.500', '0,5', '0.5'],
            'tiga desimal' => ['0.125', '0,125', '0.125'],
            'ribuan asli' => ['1000.000', '1.000', '1000'],
            'campuran' => ['1234.250', '1.234,25', '1234.25'],
        ];
    }
}
