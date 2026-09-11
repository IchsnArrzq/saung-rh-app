<?php

namespace App\Domains\Table\UseCases;

use App\Domains\Table\Enums\TableSessionStatus;
use App\Domains\Table\Repositories\TableRepository;
use App\Domains\Table\Repositories\TableSessionRepository;
use App\Events\TableSessionsChanged;
use App\Models\Table;
use App\Models\TableSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The first guest to scan a free table asks to open it. The session starts
 * Pending: the table stays free and nothing is unlocked until a cashier or
 * receptionist confirms (ApproveTableSessionUseCase). That confirmation is what
 * stops a photo of the QR from opening a table from outside the restaurant.
 *
 * Replaces CheckInTableUseCase, which opened the session — and marked the table
 * occupied — on the scan alone.
 */
class StartTableSessionUseCase
{
    public function __construct(
        private readonly TableRepository $tables,
        private readonly TableSessionRepository $sessions,
    ) {}

    public function handle(Table $table, string $customerName, int $pax): TableSession
    {
        $session = DB::transaction(function () use ($table, $customerName, $pax): TableSession {
            // Two phones scanning the same free table at once must not both open it.
            $this->tables->findForUpdate($table->id);

            if ($this->sessions->liveForTable($table->id)) {
                throw ValidationException::withMessages([
                    'joinCode' => 'Meja ini baru saja dibuka tamu lain. Minta kode gabung 4 angka dari mereka.',
                ]);
            }

            return $this->sessions->create([
                'table_id' => $table->id,
                'token' => Str::random(40),
                'join_code' => str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
                'status' => TableSessionStatus::Pending->value,
                'visibility' => 'private',
                'is_anonymous' => false,
                'customer_name' => trim($customerName),
                'pax' => $pax,
                'started_at' => now(),
            ]);
        });

        DB::afterCommit(fn () => rescue(fn () => TableSessionsChanged::dispatch()));

        return $session;
    }
}
