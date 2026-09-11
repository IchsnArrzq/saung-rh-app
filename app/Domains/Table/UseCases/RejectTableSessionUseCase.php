<?php

namespace App\Domains\Table\UseCases;

use App\Domains\Table\Enums\TableSessionStatus;
use App\Domains\Table\Repositories\TableSessionRepository;
use App\Events\TableSessionsChanged;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Staff turn a scan down — typically because nobody is sitting at that table. */
class RejectTableSessionUseCase
{
    public function __construct(private readonly TableSessionRepository $sessions) {}

    public function handle(TableSession $session, User $staff): TableSession
    {
        $session = DB::transaction(function () use ($session, $staff): TableSession {
            $locked = $this->sessions->findForUpdate($session->id);

            if (! $locked || ! $locked->status->canTransitionTo(TableSessionStatus::Rejected)) {
                throw ValidationException::withMessages([
                    'session' => 'Permintaan meja ini sudah diproses staf lain.',
                ]);
            }

            return $this->sessions->update($locked, [
                'status' => TableSessionStatus::Rejected->value,
                'closed_by' => $staff->id,
                'closed_at' => now(),
            ]);
        });

        DB::afterCommit(fn () => rescue(fn () => TableSessionsChanged::dispatch()));

        return $session;
    }
}
