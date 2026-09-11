<?php

namespace Tests\Feature\Social;

use App\Domains\Social\Enums\SpecialRequestStatus;
use App\Livewire\Admin\SpecialRequestCategories\Table as CategoryTable;
use App\Livewire\Frontend\SpecialRequestForm;
use App\Models\SpecialRequest;
use App\Models\SpecialRequestCategory;
use App\Models\Table as DiningTable;
use App\Models\TableSession;
use App\Support\TableSessionContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

/**
 * Kategori permintaan khusus kini master data (dulu enum yang dikodekan): admin
 * menambah/menonaktifkannya, tamu memilihnya di panel meja.
 */
class SpecialRequestCategoryTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

    /**
     * @return array{0: DiningTable, 1: TableSession}
     */
    private function seatedTable(): array
    {
        $table = DiningTable::create(['code' => 'T-07', 'name' => 'Meja T-07', 'capacity' => 4, 'status' => 'occupied']);
        $session = TableSession::create([
            'table_id' => $table->id,
            'token' => Str::random(40),
            'status' => 'active',
            'visibility' => 'private',
            'is_anonymous' => false,
            'started_at' => now(),
        ]);

        return [$table, $session];
    }

    /** Tamu yang sudah check-in lewat QR meja. */
    private function checkedIn(): array
    {
        [$table, $session] = $this->seatedTable();

        TableSessionContext::put($session, $table);

        return [$table, $session];
    }

    public function test_tamu_mengirim_permintaan_dengan_kategori_dari_master_data(): void
    {
        [$table] = $this->checkedIn();
        $category = SpecialRequestCategory::query()->where('slug', 'perayaan')->firstOrFail();

        Livewire::test(SpecialRequestForm::class)
            ->set('categoryId', $category->id)
            ->set('description', 'Tolong siapkan lilin ulang tahun.')
            ->call('submit')
            ->assertHasNoErrors();

        $request = SpecialRequest::query()->firstOrFail();

        $this->assertSame($category->id, $request->special_request_category_id);
        $this->assertSame(SpecialRequestStatus::Pending, $request->status);
        $this->assertSame($table->id, $request->table_id);
    }

    public function test_tamu_wajib_memilih_kategori_sendiri(): void
    {
        $this->checkedIn();

        Livewire::test(SpecialRequestForm::class)
            ->assertSet('categoryId', '')
            ->set('description', 'Minta tisu.')
            ->call('submit')
            ->assertHasErrors('categoryId');

        $this->assertSame(0, SpecialRequest::query()->count());
    }

    public function test_kategori_nonaktif_tidak_bisa_dipilih_tamu(): void
    {
        $this->checkedIn();
        $category = SpecialRequestCategory::query()->where('slug', 'suasana')->firstOrFail();
        $category->update(['is_active' => false]);

        Livewire::test(SpecialRequestForm::class)
            ->set('categoryId', $category->id)
            ->set('description', 'Kecilkan musik.')
            ->call('submit')
            ->assertHasErrors('categoryId');

        $this->assertSame(0, SpecialRequest::query()->count());
    }

    public function test_kategori_yang_sudah_dipakai_tidak_bisa_dihapus(): void
    {
        $this->actingAsRole('admin', ['special_request_category.viewAny', 'special_request_category.delete']);
        [$table, $session] = $this->seatedTable();

        $used = SpecialRequestCategory::query()->where('slug', 'dapur')->firstOrFail();
        $unused = SpecialRequestCategory::query()->where('slug', 'lainnya')->firstOrFail();

        SpecialRequest::create([
            'table_session_id' => $session->id,
            'table_id' => $table->id,
            'special_request_category_id' => $used->id,
            'description' => 'Tanpa bawang.',
            'status' => 'done',
        ]);

        Livewire::test(CategoryTable::class)->call('delete', $used->id);
        $this->assertNotNull($used->fresh(), 'Riwayat permintaan tidak boleh kehilangan kategorinya.');

        Livewire::test(CategoryTable::class)->call('delete', $unused->id);
        $this->assertNull($unused->fresh());
    }
}
