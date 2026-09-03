<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Social\Enums\SpecialRequestStatus;
use App\Domains\Social\Repositories\SpecialRequestRepository;
use App\Models\SpecialRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A waiter marks their own request done. Scoped to the assignee on purpose —
 * the waiter board must not be able to close someone else's job.
 */
class CompleteSpecialRequestUseCase
{
    public function __construct(private readonly SpecialRequestRepository $requests) {}

    public function handle(string $requestId, string $waiterId): SpecialRequest
    {
        $request = $this->requests->findAssignedTo($requestId, $waiterId);

        if (! $request) {
            throw ValidationException::withMessages([
                'request' => 'Permintaan tidak ditemukan atau bukan tugas Anda.',
            ]);
        }

        return DB::transaction(fn (): SpecialRequest => $this->requests->update($request, [
            'status' => SpecialRequestStatus::Done->value,
            'handled_at' => now(),
        ]));
    }
}
