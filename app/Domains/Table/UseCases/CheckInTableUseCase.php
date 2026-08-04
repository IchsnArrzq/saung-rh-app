<?php

namespace App\Domains\Table\UseCases;

use App\Domains\Table\Enums\TableStatus;
use App\Models\Table;
use App\Models\TableSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Opens (or reuses) an active session for a table after a valid QR scan, and
 * marks a free table occupied.
 *
 * Replaces App\Services\Customer\CheckInService.
 */
class CheckInTableUseCase
{
    public function __construct(private readonly ChangeTableStatusUseCase $changeStatus) {}

    public function handle(Table $table): TableSession
    {
        return DB::transaction(function () use ($table): TableSession {
            $session = $table->tableSessions()
                ->active()
                ->latest('started_at')
                ->first();

            if (! $session) {
                $session = $table->tableSessions()->create([
                    'token' => (string) Str::random(40),
                    'status' => 'active',
                    'visibility' => 'private',
                    'is_anonymous' => false,
                    'started_at' => now(),
                ]);

                // A fresh QR-scan session counts as a new visitor party.
                $session->visitorLogs()->create([
                    'table_id' => $table->id,
                    'recorded_by' => auth()->id(),
                    'source' => 'qr',
                    'pax' => $session->pax ?: 1,
                    'visited_at' => now(),
                ]);
            }

            if ($table->status === TableStatus::Available->value) {
                $this->changeStatus->handle($table, TableStatus::Occupied);
            }

            return $session;
        });
    }
}
