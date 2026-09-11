<?php

namespace Tests\Feature\Social;

use App\Domains\Social\Enums\SpecialRequestStatus;
use App\Livewire\Staff\FloorBoard;
use App\Models\SpecialRequest;
use App\Models\SpecialRequestCategory;
use App\Models\Table as DiningTable;
use App\Models\TableSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

/**
 * Panel meja: permintaan khusus ditangani langsung oleh staf lantai — tanpa meja
 * persetujuan manajer — lewat tombol Selesai / Tangani / Catatan.
 */
class FloorBoardTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

    private const FLOOR_PERMISSIONS = ['special_request.viewAny', 'special_request.view', 'special_request.update'];

    /**
     * @return array{0: DiningTable, 1: TableSession}
     */
    private function seatedTable(string $code = 'T-01'): array
    {
        $table = DiningTable::create(['code' => $code, 'name' => 'Meja '.$code, 'capacity' => 4, 'status' => 'occupied']);

        return [$table, $this->tableSession($table)];
    }

    private function tableSession(DiningTable $table, string $status = 'active'): TableSession
    {
        return TableSession::create([
            'table_id' => $table->id,
            'token' => Str::random(40),
            'status' => $status,
            'visibility' => 'private',
            'is_anonymous' => false,
            'started_at' => now()->subHour(),
            'closed_at' => $status === 'active' ? null : now()->subMinutes(30),
        ]);
    }

    private function specialRequest(DiningTable $table, TableSession $session, string $status = 'pending', string $description = 'Minta tambahan sendok.'): SpecialRequest
    {
        return SpecialRequest::create([
            'table_session_id' => $session->id,
            'table_id' => $table->id,
            'table_code' => $table->code,
            // Kategori bawaan dibuat oleh migrasi create_special_request_categories.
            'special_request_category_id' => SpecialRequestCategory::query()->where('slug', 'pelayanan')->value('id'),
            'description' => $description,
            'status' => $status,
        ]);
    }

    public function test_pelayan_menandai_permintaan_selesai_tanpa_persetujuan_manajer(): void
    {
        $waiter = $this->actingAsRole('waiter', self::FLOOR_PERMISSIONS);
        [$table, $session] = $this->seatedTable();
        $request = $this->specialRequest($table, $session);

        Livewire::test(FloorBoard::class)
            ->call('open', $table->id)
            ->assertSee('Minta tambahan sendok.')
            ->call('completeRequest', $request->id)
            ->assertHasNoErrors();

        $request->refresh();

        $this->assertSame(SpecialRequestStatus::Done, $request->status);
        $this->assertSame($waiter->id, $request->assigned_to, 'Yang menangani tercatat — dasar skor KPI staf.');
        $this->assertNotNull($request->handled_at);
    }

    public function test_menolak_permintaan_wajib_menulis_alasan_untuk_tamu(): void
    {
        $this->actingAsRole('waiter', self::FLOOR_PERMISSIONS);
        [$table, $session] = $this->seatedTable();
        $request = $this->specialRequest($table, $session);

        $board = Livewire::test(FloorBoard::class)
            ->call('open', $table->id)
            ->call('startNote', $request->id)
            ->call('rejectRequest')
            ->assertHasErrors('note');

        $this->assertSame(SpecialRequestStatus::Pending, $request->refresh()->status);

        $board->set('note', 'Kue ulang tahun sedang habis.')
            ->call('rejectRequest')
            ->assertHasNoErrors();

        $request->refresh();

        $this->assertSame(SpecialRequestStatus::Rejected, $request->status);
        $this->assertSame('Kue ulang tahun sedang habis.', $request->staff_note);
    }

    public function test_permintaan_yang_sudah_selesai_tidak_bisa_dibuka_ulang(): void
    {
        $this->actingAsRole('waiter', self::FLOOR_PERMISSIONS);
        [$table, $session] = $this->seatedTable();
        $request = $this->specialRequest($table, $session, 'done');

        Livewire::test(FloorBoard::class)
            ->call('claimRequest', $request->id)
            ->assertHasErrors('panel');

        $this->assertSame(SpecialRequestStatus::Done, $request->refresh()->status);
    }

    public function test_peran_tanpa_izin_permintaan_khusus_tidak_bisa_membuka_panel_meja(): void
    {
        $this->actingAsRole('chef', ['kitchen.view']);

        $this->get(route('floor.index'))->assertForbidden();
    }

    public function test_kartu_meja_hanya_menghitung_permintaan_dari_sesi_yang_masih_aktif(): void
    {
        $this->actingAsRole('waiter', self::FLOOR_PERMISSIONS);
        [$table, $session] = $this->seatedTable();
        $this->specialRequest($table, $session);

        // Sisa rombongan sebelumnya: masih "menunggu", tapi sesinya sudah ditutup.
        $this->specialRequest($table, $this->tableSession($table, 'closed'), 'pending', 'Permintaan tamu kemarin.');

        Livewire::test(FloorBoard::class)
            ->assertSee('1 permintaan')
            ->assertDontSee('2 permintaan');
    }
}
