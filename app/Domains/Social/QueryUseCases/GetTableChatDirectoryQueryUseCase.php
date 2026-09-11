<?php

namespace App\Domains\Social\QueryUseCases;

use App\Domains\Table\Enums\TableStatus;
use App\Domains\Table\Repositories\TableRepository;
use App\Models\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * Siapa yang bisa diajak mengobrol dari panel meja tamu: meja lain yang sedang
 * terisi. Obrolan antar-meja hanya ke meja yang memang ada tamunya sekarang.
 *
 * Membaca TableRepository milik domain Table — repository adalah kontrak baca
 * yang boleh dipakai lintas domain (AGENTS.md § Feature First Structure).
 */
class GetTableChatDirectoryQueryUseCase
{
    private const OCCUPIED = [TableStatus::Occupied, TableStatus::OrderIn];

    public function __construct(private readonly TableRepository $tables) {}

    /**
     * @return Collection<int, Table>
     */
    public function occupiedExcept(string $tableId): Collection
    {
        return $this->tables->inStatusesExcept($this->occupiedValues(), $tableId);
    }

    /** Postgres menolak string non-UUID di kolom uuid dengan error — disaring dulu. */
    public function find(string $tableId): ?Table
    {
        return Str::isUuid($tableId) ? $this->tables->find($tableId) : null;
    }

    public function isOccupied(string $tableId): bool
    {
        $table = $this->find($tableId);

        return $table !== null && in_array((string) $table->status, $this->occupiedValues(), true);
    }

    /**
     * @return array<int, string>
     */
    private function occupiedValues(): array
    {
        return array_map(fn (TableStatus $status) => $status->value, self::OCCUPIED);
    }
}
