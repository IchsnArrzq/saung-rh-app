<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Social\Enums\SpecialRequestStatus;
use App\Domains\Social\Repositories\SpecialRequestRepositoryInterface;
use App\Models\SpecialRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RejectSpecialRequestUseCase
{
    public function __construct(private readonly SpecialRequestRepositoryInterface $requests) {}

    public function handle(string $requestId, User $manager): SpecialRequest
    {
        $request = $this->requests->find($requestId);

        if (! $request) {
            throw ValidationException::withMessages(['request' => 'Permintaan tidak ditemukan.']);
        }

        return DB::transaction(fn (): SpecialRequest => $this->requests->update($request, [
            'status' => SpecialRequestStatus::Rejected->value,
            'approved_by' => $manager->id,
            'handled_at' => now(),
        ]));
    }
}
