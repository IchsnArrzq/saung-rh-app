<?php

namespace App\Domains\Employee\UseCases;

use App\Domains\Employee\Repositories\StaffActivityRepository;
use App\Models\Tip;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogTipUseCase
{
    public function __construct(private readonly StaffActivityRepository $activity) {}

    public function handle(
        float $amount,
        ?string $tableId = null,
        ?string $orderId = null,
        ?string $note = null,
        ?string $waiterId = null,
    ): Tip {
        return DB::transaction(fn (): Tip => $this->activity->createTip([
            'waiter_id' => $waiterId ?? Auth::id(),
            'table_id' => $tableId ?: null,
            'order_id' => $orderId ?: null,
            'amount' => $amount,
            'note' => trim((string) $note) !== '' ? trim((string) $note) : null,
            'received_at' => now(),
        ]));
    }
}
