<?php

namespace App\Services\Customer;

use App\Models\Table;
use App\Models\TableSession;
use App\Models\TableStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckInService
{
    /**
     * Open (or reuse) an active session for a table after a valid QR scan,
     * and mark the table occupied when it is currently available.
     */
    public function checkIn(Table $table): TableSession
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

            if ($table->tableStatus?->key === 'available') {
                $occupied = TableStatus::query()->where('key', 'occupied')->first();

                if ($occupied) {
                    $table->update(['table_status_id' => $occupied->id]);
                }
            }

            return $session;
        });
    }
}
