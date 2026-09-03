<?php

namespace App\Domains\Table\QueryUseCases;

use App\Domains\Table\Enums\TableStatus;
use App\Domains\Table\Repositories\TableRepository;
use App\Models\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GetTableListQueryUseCase
{
    /** Bucket for rows whose status column is somehow blank. */
    private const UNASSIGNED = '__unassigned__';

    public function __construct(private readonly TableRepository $tables) {}

    public function paginate(string $search = '', int $perPage = 12): LengthAwarePaginator
    {
        return $this->tables->paginateForAdmin($perPage, $search);
    }

    /** Whole floor, for the status board and table map. */
    public function all(): Collection
    {
        return $this->tables->allOrdered();
    }

    /** Whole floor filtered by code/name — floor tools never paginate. */
    public function search(string $search = ''): Collection
    {
        return $this->tables->search($search);
    }

    /** Only free tables — customer picker and booking form. */
    public function selectable(): Collection
    {
        return $this->tables->selectable();
    }

    /**
     * The customer table picker: every table grouped under its status column,
     * plus the status list itself in board order.
     *
     * @return array{
     *     statuses: \Illuminate\Support\Collection<int, TableStatus>,
     *     tablesByStatus: \Illuminate\Support\Collection<string, Collection<int, Table>>,
     *     unassignedTables: \Illuminate\Support\Collection<int, Table>
     * }
     */
    public function groupedByStatus(string $search = ''): array
    {
        $tablesByStatus = $this->tables->search($search)
            ->groupBy(fn ($table) => $table->status ?: self::UNASSIGNED);

        return [
            'statuses' => collect(TableStatus::cases())
                ->sortBy(fn (TableStatus $status) => $status->sortOrder())
                ->values(),
            'tablesByStatus' => $tablesByStatus,
            'unassignedTables' => $tablesByStatus->get(self::UNASSIGNED, collect()),
        ];
    }
}
