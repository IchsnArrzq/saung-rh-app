<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Services\Customer\CheckInService;
use App\Support\TableSessionContext;
use Illuminate\Http\RedirectResponse;

class CheckInController extends Controller
{
    public function __invoke(string $token, CheckInService $service): RedirectResponse
    {
        $table = Table::query()
            ->with('tableStatus')
            ->where('qr_token', $token)
            ->first();

        abort_if($table === null, 404, 'QR meja tidak valid atau sudah kedaluwarsa.');

        $session = $service->checkIn($table);

        TableSessionContext::put($session, $table);

        return redirect()
            ->route('public.menu', ['mode' => 'offline', 'table_id' => $table->id])
            ->with('success', 'Check-in meja '.($table->code ?? $table->name).' berhasil.');
    }
}
