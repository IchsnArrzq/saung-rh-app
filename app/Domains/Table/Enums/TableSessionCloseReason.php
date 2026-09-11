<?php

namespace App\Domains\Table\Enums;

/** Why a session that was once live has ended — shown in the session history. */
enum TableSessionCloseReason: string
{
    case BillsCleared = 'bills_cleared';
    case TableReleased = 'table_released';
    case Deactivated = 'deactivated';

    public function label(): string
    {
        return match ($this) {
            self::BillsCleared => 'Tagihan lunas',
            self::TableReleased => 'Meja dikosongkan',
            self::Deactivated => 'Dinonaktifkan staf',
        };
    }
}
