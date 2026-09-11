<?php

namespace App\Livewire\Frontend;

use App\Domains\Table\QueryUseCases\FindTableQueryUseCase;
use App\Domains\Table\QueryUseCases\GetTableSessionQueryUseCase;
use App\Domains\Table\UseCases\StartTableSessionUseCase;
use App\Models\TableSession;
use App\Support\TableSessionContext;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Where a table's QR code lands (/t/{token}). What the guest sees depends on
 * the table and on this phone:
 *
 *  - start    the table is free: name + party size, then wait for staff
 *  - join     the table is already open: type the code shown on the first phone
 *  - waiting  this phone asked to open the table; polls until staff decide
 *  - active   this phone's session is confirmed — on to the menu
 *
 * Nothing here trusts the QR alone: a photo of it taken home can at most ask
 * staff to open a table nobody is sitting at, and staff will turn it down.
 */
#[Layout('layouts.guest')]
class TableCheckIn extends Component
{
    /** Wrong join codes allowed per table and device before a one-minute pause. */
    private const JOIN_ATTEMPTS = 5;

    #[Locked]
    public string $tableId = '';

    public string $customerName = '';

    public ?int $pax = null;

    public string $joinCode = '';

    public function mount(string $token, FindTableQueryUseCase $findTable, GetTableSessionQueryUseCase $sessions)
    {
        $table = $findTable->byQrToken($token);

        abort_if($table === null, 404);

        $this->tableId = $table->id;

        return $this->mySession($sessions)?->isActive()
            ? $this->redirectRoute('public.menu')
            : null;
    }

    public function start(StartTableSessionUseCase $start, FindTableQueryUseCase $findTable): void
    {
        $validated = $this->validate([
            'customerName' => ['required', 'string', 'max:60'],
            'pax' => ['required', 'integer', 'min:1', 'max:50'],
        ], [
            'customerName.required' => 'Isi nama pemesan.',
            'customerName.max' => 'Nama pemesan paling panjang 60 huruf.',
            'pax.required' => 'Isi jumlah orang di meja.',
            'pax.integer' => 'Jumlah orang harus angka.',
            'pax.min' => 'Jumlah orang minimal 1.',
            'pax.max' => 'Jumlah orang paling banyak 50.',
        ]);

        $table = $findTable->byId($this->tableId);

        abort_if($table === null, 404);

        // Throws on `joinCode` if another phone opened the table a moment ago —
        // the next render then shows the join form with that message.
        $session = $start->handle($table, $validated['customerName'], (int) $validated['pax']);

        TableSessionContext::put($session, $table);
        $this->reset('customerName', 'pax');
    }

    public function join(GetTableSessionQueryUseCase $sessions)
    {
        $this->validate(['joinCode' => ['required', 'digits:4']], [
            'joinCode.required' => 'Masukkan kode gabung 4 angka.',
            'joinCode.digits' => 'Kode gabung terdiri dari 4 angka.',
        ]);

        $key = 'table-join:'.$this->tableId.'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, self::JOIN_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'joinCode' => 'Terlalu banyak kode yang salah. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.',
            ]);
        }

        $session = $sessions->joinable($this->tableId, $this->joinCode);

        if (! $session) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'joinCode' => 'Kode gabung tidak cocok. Lihat kode 4 angka di HP teman yang pertama scan.',
            ]);
        }

        RateLimiter::clear($key);
        TableSessionContext::put($session, $session->table);
        $this->reset('joinCode');

        return $session->isActive() ? $this->redirectRoute('public.menu') : null;
    }

    /** Polled while waiting — moves on the moment staff confirm. */
    public function checkStatus(GetTableSessionQueryUseCase $sessions)
    {
        return $this->mySession($sessions)?->isActive()
            ? $this->redirectRoute('public.menu')
            : null;
    }

    public function render(FindTableQueryUseCase $findTable, GetTableSessionQueryUseCase $sessions): View
    {
        $mine = $this->mySession($sessions);
        $live = $sessions->liveForTable($this->tableId);

        $state = match (true) {
            (bool) $mine?->isActive() => 'active',
            (bool) $mine?->isPending() => 'waiting',
            $live !== null => 'join',
            default => 'start',
        };

        return view('livewire.frontend.table-check-in', [
            'table' => $findTable->byId($this->tableId),
            'state' => $state,
            'session' => $mine,
        ]);
    }

    /** This phone's session — whatever its status — but only if it is for this table. */
    private function mySession(GetTableSessionQueryUseCase $sessions): ?TableSession
    {
        $session = $sessions->find(TableSessionContext::sessionId());

        return $session?->table_id === $this->tableId ? $session : null;
    }
}
