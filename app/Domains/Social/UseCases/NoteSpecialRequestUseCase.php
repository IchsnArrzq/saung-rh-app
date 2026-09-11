<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Social\Repositories\SpecialRequestRepository;
use App\Events\FloorActivity;
use App\Models\SpecialRequest;
use App\Support\LiveUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Staf menambah atau mengganti catatan pada permintaan tanpa mengubah
 * statusnya — mis. "kue diantar setelah hidangan utama". Catatannya tampil di
 * panel meja tamu.
 */
class NoteSpecialRequestUseCase
{
    public function __construct(private readonly SpecialRequestRepository $requests) {}

    public function handle(string $requestId, string $note): SpecialRequest
    {
        $request = $this->requests->find($requestId);

        if (! $request) {
            throw ValidationException::withMessages(['request' => 'Permintaan tidak ditemukan. Muat ulang panel.']);
        }

        $note = trim($note);

        if ($note === '') {
            throw ValidationException::withMessages(['note' => 'Catatan masih kosong.']);
        }

        $request = DB::transaction(fn (): SpecialRequest => $this->requests->update($request, ['staff_note' => $note]));

        DB::afterCommit(fn () => LiveUpdate::send(
            new FloorActivity($request->table_id, FloorActivity::REQUEST, FloorActivity::UPDATED, $request->table_code),
        ));

        return $request;
    }
}
