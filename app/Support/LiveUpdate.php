<?php

namespace App\Support;

/**
 * Kirim event siaran (Reverb) tanpa menjatuhkan aksi yang memicunya.
 *
 * Siaran realtime adalah pelengkap: kalau server Reverb mati, tamu tetap harus
 * bisa mengirim permintaan dan pelayan tetap harus bisa menandainya selesai —
 * layar lain menyusul lewat polling cadangan. Kegagalannya tetap dicatat
 * (rescue() melaporkan exception ke log), tidak ditelan diam-diam.
 *
 * Event yang dikirim lewat sini memakai ShouldBroadcastNow: aplikasi ini tidak
 * menjalankan queue worker, dan siaran yang diantrekan tidak pernah sampai ke
 * browser (itulah sebab obrolan meja dulu tidak pernah realtime).
 */
class LiveUpdate
{
    public static function send(object $event): void
    {
        rescue(function () use ($event): void {
            event($event);
        });
    }
}
