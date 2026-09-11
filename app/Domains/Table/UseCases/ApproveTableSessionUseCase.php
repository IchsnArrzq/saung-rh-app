<?php

namespace App\Domains\Table\UseCases;

use App\Domains\Table\Enums\TableSessionStatus;
use App\Domains\Table\Enums\TableStatus;
use App\Domains\Table\Repositories\TableSessionRepository;
use App\Events\TableSessionsChanged;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Staff confirm the guest who scanned is really seated at that table. Only now
 * does the table count as occupied and the party as a visit — the scan alone
 * used to do both.
 */
class ApproveTableSessionUseCase
{
    public function __construct(
        private readonly TableSessionRepository $sessions,
        private readonly ChangeTableStatusUseCase $changeStatus,
    ) {}

    public function handle(TableSession $session, User $approver): TableSession
    {
        $session = DB::transaction(function () use ($session, $approver): TableSession {
            $locked = $this->sessions->findForUpdate($session->id);

            if (! $locked || ! $locked->status->canTransitionTo(TableSessionStatus::Active)) {
                throw ValidationException::withMessages([
                    'session' => 'Permintaan meja ini sudah diproses staf lain.',
                ]);
            }

            $this->sessions->update($locked, [
                'status' => TableSessionStatus::Active->value,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            $locked->visitorLogs()->create([
                'table_id' => $locked->table_id,
                'recorded_by' => $approver->id,
                'source' => 'qr',
                'pax' => $locked->pax ?: 1,
                'visited_at' => now(),
            ]);

            $table = $locked->table;

            if ($table && $table->status === TableStatus::Available->value) {
                $this->changeStatus->handle($table, TableStatus::Occupied);
            }

            return $locked;
        });

        DB::afterCommit(fn () => rescue(fn () => TableSessionsChanged::dispatch()));

        return $session;
    }
}
