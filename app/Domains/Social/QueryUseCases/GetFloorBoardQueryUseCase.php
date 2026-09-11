<?php

namespace App\Domains\Social\QueryUseCases;

use App\Domains\Social\Repositories\SongRequestRepository;
use App\Domains\Social\Repositories\SpecialRequestRepository;
use App\Domains\Social\Services\ChatService;
use App\Domains\Table\Repositories\TableRepository;
use App\Models\SongRequest;
use App\Models\SpecialRequest;
use App\Models\Table;
use App\Models\TableSession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Panel meja staf: satu kartu per meja dengan yang sedang terjadi di sana —
 * permintaan khusus terbuka, lagu yang antre, obrolan tamu yang belum dibaca —
 * dan rincian satu meja saat kartunya dibuka.
 *
 * Membaca TableRepository milik domain Table: repository adalah kontrak baca
 * yang boleh dipakai lintas domain (AGENTS.md § Feature First Structure).
 */
class GetFloorBoardQueryUseCase
{
    public function __construct(
        private readonly TableRepository $tables,
        private readonly SpecialRequestRepository $requests,
        private readonly SongRequestRepository $songs,
        private readonly ChatService $chat,
    ) {}

    /**
     * Satu kartu per meja, urut kode. Semua angka dihitung dari sesi QR yang
     * masih aktif — sisa sesi tamu sebelumnya bukan "yang terjadi sekarang".
     *
     * @return Collection<int, array{table: Table, session: ?TableSession, open_requests: int, active_songs: int, unread_chat: bool}>
     */
    public function tiles(string $staffId): Collection
    {
        $requestCounts = $this->requests->openCountsByTable();
        $songCounts = $this->songs->activeCountsByTable();
        $chatAvailable = $this->chat->available();
        $seen = $chatAvailable ? $this->chat->roomSeenMap($staffId) : [];

        return $this->tables->allWithActiveSession()
            ->map(function (Table $table) use ($requestCounts, $songCounts, $chatAvailable, $seen): array {
                $id = (string) $table->id;
                $session = $table->tableSessions->first();

                return [
                    'table' => $table,
                    'session' => $session,
                    'open_requests' => (int) $requestCounts->get($id, 0),
                    'active_songs' => (int) $songCounts->get($id, 0),
                    'unread_chat' => $chatAvailable && $session !== null
                        && $this->isUnread($this->chat->roomLastMessage($id), $seen[$id] ?? null, $session),
                ];
            })
            ->values();
    }

    /**
     * Rincian satu meja untuk panel samping; null bila mejanya tidak ada.
     *
     * @return array{table: Table, session: ?TableSession, open_requests: \Illuminate\Database\Eloquent\Collection, closed_requests: \Illuminate\Database\Eloquent\Collection, active_songs: \Illuminate\Database\Eloquent\Collection, finished_songs: \Illuminate\Database\Eloquent\Collection, chat_available: bool, messages: array<int, array<string, mixed>>}|null
     */
    public function detail(string $tableId): ?array
    {
        $table = $this->tables->findWithActiveSession($tableId);

        if (! $table) {
            return null;
        }

        $chatAvailable = $this->chat->available();

        return [
            'table' => $table,
            'session' => $table->tableSessions->first(),
            'open_requests' => $this->requests->openForTable($tableId),
            'closed_requests' => $this->requests->recentlyClosedForTable($tableId),
            'active_songs' => $this->songs->activeForTable($tableId),
            'finished_songs' => $this->songs->recentlyFinishedForTable($tableId),
            'chat_available' => $chatAvailable,
            // Obrolan ditampilkan walau sesinya sudah tutup: justru saat itulah
            // staf perlu melihat sisa pesan tamu sebelumnya untuk dibersihkan.
            'messages' => $chatAvailable ? $this->chat->messages($tableId) : [],
        ];
    }

    public function table(string $id): ?Table
    {
        return $this->tables->findWithActiveSession($id);
    }

    public function request(string $id): ?SpecialRequest
    {
        return $this->requests->find($id);
    }

    public function song(string $id): ?SongRequest
    {
        return $this->songs->find($id);
    }

    /**
     * Pesan terakhir di ruang meja datang dari tamu, ditulis selama sesi yang
     * sekarang, dan setelah staf ini terakhir membukanya.
     *
     * @param  array<string, mixed>|null  $last
     */
    private function isUnread(?array $last, ?string $seenAt, TableSession $session): bool
    {
        if (! $last || ! empty($last['staff']) || empty($last['at'])) {
            return false;
        }

        try {
            $at = Carbon::parse((string) $last['at']);
        } catch (\Throwable $e) {
            return false;
        }

        if ($session->started_at && $at->lessThan($session->started_at)) {
            return false;
        }

        if (! $seenAt) {
            return true;
        }

        try {
            return $at->greaterThan(Carbon::parse($seenAt));
        } catch (\Throwable $e) {
            return true;
        }
    }
}
