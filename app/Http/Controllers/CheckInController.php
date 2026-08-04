<?php

namespace App\Http\Controllers;

use App\Domains\Table\UseCases\CheckInTableUseCase;
use App\Models\Table;
use App\Support\TableSessionContext;
use Illuminate\Http\RedirectResponse;

class CheckInController extends Controller
{
    public function __invoke(string $token, CheckInTableUseCase $checkIn): RedirectResponse
    {
        $table = Table::query()
            ->where('qr_token', $token)
            ->first();

        abort_if($table === null, 404, 'QR meja tidak valid atau sudah kedaluwarsa.');

        $session = $checkIn->handle($table);

        TableSessionContext::put($session, $table);

        return redirect()
            ->route('public.menu', ['mode' => 'offline', 'table_id' => $table->id])
            ->with('success', 'Check-in meja '.($table->code ?? $table->name).' berhasil.');
    }
}
