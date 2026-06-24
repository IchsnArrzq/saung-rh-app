<?php

namespace App\Support;

use App\Models\Table;
use App\Models\TableSession;

/**
 * Stores the active table-session (from QR check-in) in the browser session.
 * Used as the "physical presence" binding for inter-table chat & song requests.
 */
class TableSessionContext
{
    public const KEY = 'table_session';

    public static function put(TableSession $session, Table $table): void
    {
        session()->put(self::KEY, [
            'session_id' => $session->id,
            'token' => $session->token,
            'table_id' => $table->id,
            'table_code' => $table->code,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function current(): ?array
    {
        return session()->get(self::KEY);
    }

    public static function activeSession(): ?TableSession
    {
        $context = self::current();

        if (! $context || empty($context['session_id'])) {
            return null;
        }

        return TableSession::query()
            ->active()
            ->find($context['session_id']);
    }

    public static function clear(): void
    {
        session()->forget(self::KEY);
    }
}
