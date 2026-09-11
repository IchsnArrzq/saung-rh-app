<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Social\Enums\SpecialRequestStatus;
use App\Domains\Social\Repositories\SpecialRequestRepository;
use App\Events\FloorActivity;
use App\Models\SpecialRequest;
use App\Models\SpecialRequestCategory;
use App\Models\TableSession;
use App\Support\LiveUpdate;
use Illuminate\Support\Facades\DB;

/**
 * A table sends a special request straight to the floor staff — no manager
 * approval in between. It lands on the staff Panel meja the moment it commits.
 */
class SubmitSpecialRequestUseCase
{
    public function __construct(private readonly SpecialRequestRepository $requests) {}

    public function handle(
        TableSession $session,
        SpecialRequestCategory $category,
        string $description,
        bool $isPaid = false,
        ?float $price = null,
    ): SpecialRequest {
        $request = DB::transaction(fn (): SpecialRequest => $this->requests->create([
            'table_session_id' => $session->id,
            'table_id' => $session->table_id,
            'table_code' => $session->table?->code,
            'requested_by' => $session->customer_name,
            'special_request_category_id' => $category->id,
            'description' => trim($description),
            'is_paid' => $isPaid,
            // A price only means something on a paid request.
            'price' => $isPaid ? $price : null,
            'status' => SpecialRequestStatus::Pending->value,
        ]));

        DB::afterCommit(fn () => LiveUpdate::send(
            new FloorActivity($request->table_id, FloorActivity::REQUEST, FloorActivity::NEW, $request->table_code),
        ));

        return $request;
    }
}
