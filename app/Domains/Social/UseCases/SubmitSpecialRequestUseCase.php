<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Social\Enums\SpecialRequestCategory;
use App\Domains\Social\Enums\SpecialRequestStatus;
use App\Domains\Social\Repositories\SpecialRequestRepositoryInterface;
use App\Models\SpecialRequest;
use App\Models\TableSession;
use Illuminate\Support\Facades\DB;

class SubmitSpecialRequestUseCase
{
    public function __construct(private readonly SpecialRequestRepositoryInterface $requests) {}

    public function handle(
        TableSession $session,
        SpecialRequestCategory $category,
        string $description,
        bool $isPaid = false,
        ?float $price = null,
    ): SpecialRequest {
        return DB::transaction(fn (): SpecialRequest => $this->requests->create([
            'table_session_id' => $session->id,
            'table_id' => $session->table_id,
            'table_code' => $session->table?->code,
            'requested_by' => $session->customer_name,
            'category' => $category->value,
            'description' => trim($description),
            'is_paid' => $isPaid,
            // A price only means something on a paid request.
            'price' => $isPaid ? $price : null,
            'status' => SpecialRequestStatus::Pending->value,
        ]));
    }
}
