<?php

namespace App\Services\Tables;

use App\Models\Table;

/**
 * Handles table turnover once a party has paid and left: the table moves to
 * "cleaning" (so the OB crew can prep it) and any active QR session is closed.
 */
interface TableTurnoverServiceInterface
{
    public function release(Table $table): void;
}
