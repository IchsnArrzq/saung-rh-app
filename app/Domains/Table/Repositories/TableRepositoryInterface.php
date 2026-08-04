<?php

namespace App\Domains\Table\Repositories;

use App\Models\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TableRepositoryInterface
{
    public function find(string $id): ?Table;

    /** Tables in a given status, ordered by code. */
    public function byStatus(string $status): Collection;

    public function countByStatus(string $status): int;

    /** Every table for the floor board / pickers, category eager-loaded. */
    public function allOrdered(): Collection;

    /** Full (unpaginated) list filtered by code/name — floor tools show every table. */
    public function search(string $search): Collection;

    /** Free tables only — what a customer may pick or book. */
    public function selectable(): Collection;

    public function paginateForAdmin(int $perPage = 12, string $search = ''): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Table $table, array $attributes): Table;

    /** Ends every active QR session on a table. */
    public function closeActiveSessions(Table $table): void;
}
