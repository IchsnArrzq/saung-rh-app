<?php

namespace App\Domains\Table\Listeners;

use App\Domains\Order\Events\TableBillsCleared;
use App\Domains\Table\Repositories\TableRepository;
use App\Domains\Table\UseCases\ReleaseTableUseCase;

/**
 * Frees a table once the Order domain reports it owes nothing.
 *
 * Whether any bill is still outstanding is Order's question; what "free" means
 * for a table — cleaning, sessions closed — is this domain's.
 */
class ReleaseTableOnBillsCleared
{
    public function __construct(
        private readonly TableRepository $tables,
        private readonly ReleaseTableUseCase $releaseTable,
    ) {}

    public function handle(TableBillsCleared $event): void
    {
        $table = $this->tables->find($event->tableId);

        if (! $table) {
            return;
        }

        $this->releaseTable->handle($table);
    }
}
