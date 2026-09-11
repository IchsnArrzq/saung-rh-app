<?php

namespace App\Domains\Table\UseCases;

use App\Domains\Table\Enums\TableSessionCloseReason;
use App\Domains\Table\Enums\TableSessionStatus;
use App\Domains\Table\Repositories\TableSessionRepository;
use App\Events\TableSessionsChanged;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Staff end a session by hand — the party left without paying, or a phone that
 * has no business at the table got in. Every guest action re-checks the
 * session's status, so the next order, chat or request from it is refused.
 *
 * The table's own status is left alone: whether it now needs cleaning is a
 * separate call staff make on the floor board.
 */
class CloseTableSessionUseCase
{
    public function __construct(private readonly TableSessionRepository $sessions) {}

    public function handle(TableSession $session, User $staff): TableSession
    {
        $session = DB::transaction(function () use ($session, $staff): TableSession {
            $locked = $this->sessions->findForUpdate($session->id);

            if (! $locked || ! $locked->status->canTransitionTo(TableSessionStatus::Closed)) {
                throw ValidationException::withMessages([
                    'session' => 'Sesi meja ini sudah berakhir.',
                ]);
            }

            return $this->sessions->update($locked, [
                'status' => TableSessionStatus::Closed->value,
                'close_reason' => TableSessionCloseReason::Deactivated->value,
                'closed_by' => $staff->id,
                'closed_at' => now(),
            ]);
        });

        DB::afterCommit(fn () => rescue(fn () => TableSessionsChanged::dispatch()));

        return $session;
    }
}
