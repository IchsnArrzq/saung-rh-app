<?php

namespace App\Support;

use App\Models\Table;
use App\Models\TableSession;

/**
 * Remembers, in this browser's server-side session, which QR table session the
 * device belongs to. It only ever holds the id — whether that session is still
 * waiting, active or over is read fresh from the database on every action
 * (GetTableSessionQueryUseCase), so a phone taken home loses access the moment
 * staff close the session, not when its cookie expires.
 */
class TableSessionContext
{
    public const KEY = 'table_session';

    public static function put(TableSession $session, Table $table): void
    {
        session()->put(self::KEY, [
            'session_id' => $session->id,
            'table_id' => $table->id,
            'table_code' => $table->code,
            'qr_token' => $table->qr_token,
        ]);
    }

    /**
     * @return array{session_id: string, table_id: string, table_code: ?string, qr_token: ?string}|null
     */
    public static function current(): ?array
    {
        return session()->get(self::KEY);
    }

    public static function sessionId(): ?string
    {
        return self::current()['session_id'] ?? null;
    }

    public static function clear(): void
    {
        session()->forget(self::KEY);
    }
}
