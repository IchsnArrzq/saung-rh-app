<?php

namespace App\Services\Customer;

use App\Models\Table;
use App\Models\TableSession;

interface CheckInServiceInterface
{
    /**
     * Open (or reuse) an active session for a table after a valid QR scan,
     * and mark the table occupied when it is currently available.
     */
    public function checkIn(Table $table): TableSession;
}
