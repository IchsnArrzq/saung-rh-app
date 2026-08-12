<?php

namespace App\Domains\Employee\UseCases;

use App\Domains\Employee\Enums\ServiceType;
use App\Domains\Employee\Repositories\StaffActivityRepositoryInterface;
use App\Models\ServiceLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogServiceUseCase
{
    public function __construct(private readonly StaffActivityRepositoryInterface $activity) {}

    public function handle(
        ServiceType $type,
        ?string $tableId = null,
        ?string $description = null,
        ?string $waiterId = null,
    ): ServiceLog {
        return DB::transaction(fn (): ServiceLog => $this->activity->createServiceLog([
            'waiter_id' => $waiterId ?? Auth::id(),
            'table_id' => $tableId ?: null,
            'type' => $type->value,
            'description' => trim((string) $description) !== '' ? trim((string) $description) : null,
            'served_at' => now(),
        ]));
    }
}
