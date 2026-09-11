<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Social\Enums\SpecialRequestStatus;
use App\Domains\Social\Repositories\SpecialRequestRepository;
use App\Events\FloorActivity;
use App\Models\SpecialRequest;
use App\Models\User;
use App\Support\LiveUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Staf lantai memindahkan permintaan khusus: tangani → selesai, atau tolak.
 *
 * Tidak ada meja manajer lagi — siapa pun yang lolos `special_request.update`
 * boleh menanganinya (dijaga di pemanggil lewat Policy), dan dialah yang
 * tercatat di `assigned_to`, dasar skor KPI staf. Transisi yang sah milik Enum;
 * menolak wajib disertai catatan karena catatan itulah yang dibaca tamu.
 */
class ChangeSpecialRequestStatusUseCase
{
    public function __construct(private readonly SpecialRequestRepository $requests) {}

    public function handle(string $requestId, SpecialRequestStatus $target, User $staff, ?string $note = null): SpecialRequest
    {
        $request = $this->requests->find($requestId);

        if (! $request) {
            throw ValidationException::withMessages(['request' => 'Permintaan tidak ditemukan. Muat ulang panel.']);
        }

        if (! $request->status->canTransitionTo($target)) {
            throw ValidationException::withMessages([
                'request' => 'Permintaan ini sudah '.mb_strtolower($request->status->label()).' — mungkin baru saja ditangani staf lain.',
            ]);
        }

        $note = trim((string) $note);

        if ($target === SpecialRequestStatus::Rejected && $note === '') {
            throw ValidationException::withMessages([
                'note' => 'Tulis alasan singkat supaya tamu tahu kenapa permintaannya tidak bisa dipenuhi.',
            ]);
        }

        $attributes = [
            'status' => $target->value,
            'assigned_to' => $staff->id,
        ];

        if ($note !== '') {
            $attributes['staff_note'] = $note;
        }

        if (! $target->isOpen()) {
            $attributes['handled_at'] = now();
        }

        $request = DB::transaction(fn (): SpecialRequest => $this->requests->update($request, $attributes));

        DB::afterCommit(fn () => LiveUpdate::send(
            new FloorActivity($request->table_id, FloorActivity::REQUEST, FloorActivity::UPDATED, $request->table_code),
        ));

        return $request;
    }
}
