<?php

namespace App\Services\Tables;

use App\Models\Table;
use App\Models\TableStatus;

/**
 * Handles table turnover once a party has paid and left: the table moves to
 * "cleaning" (so the OB crew can prep it) and any active QR session is closed.
 */
class TableTurnoverService
{
    public function release(Table $table): void
    {
        $target = TableStatus::query()->where('key', 'cleaning')->first()
            ?? TableStatus::query()->where('key', 'available')->first();

        if ($target && $table->table_status_id !== $target->id) {
            $table->update(['table_status_id' => $target->id]);
        }

        // Close the QR check-in session(s) so chat/song/special-request access ends.
        $table->tableSessions()
            ->where('status', 'active')
            ->update(['status' => 'closed', 'closed_at' => now()]);
    }
}
