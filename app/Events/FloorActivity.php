<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * "Ada yang berubah di meja ini" — permintaan khusus, lagu, atau obrolan.
 *
 * Dua pendengar:
 *  - `private-floor` : Panel meja staf, untuk menyegarkan kartu meja,
 *                      menyalakan titik penanda, dan memunculkan notifikasi
 *                      saat ada permintaan baru (`action` = new).
 *  - `table.{id}`    : panel meja tamu di meja itu, supaya status permintaan
 *                      dan lagunya berubah tanpa dimuat ulang. Kanal publik
 *                      karena tamu QR tidak login — isinya hanya id & kode meja
 *                      dan jenis perubahannya, tanpa isi permintaan.
 *
 * Kirim lewat App\Support\LiveUpdate::send(), bukan dispatch() langsung.
 */
class FloorActivity implements ShouldBroadcastNow
{
    use Dispatchable;

    public const REQUEST = 'request';

    public const SONG = 'song';

    public const CHAT = 'chat';

    /** Tamu baru saja mengirim sesuatu — layak jadi notifikasi staf. */
    public const NEW = 'new';

    /** Staf mengubah sesuatu yang sudah ada. */
    public const UPDATED = 'updated';

    public function __construct(
        public ?string $tableId,
        public string $kind,
        public string $action = self::UPDATED,
        public ?string $tableCode = null,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('floor')];

        if ($this->tableId) {
            $channels[] = new Channel('table.'.$this->tableId);
        }

        return $channels;
    }

    /**
     * @return array<string, string|null>
     */
    public function broadcastWith(): array
    {
        return [
            'table_id' => $this->tableId,
            'table_code' => $this->tableCode,
            'kind' => $this->kind,
            'action' => $this->action,
        ];
    }
}
