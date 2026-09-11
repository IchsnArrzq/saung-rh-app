<?php

namespace App\Domains\Social\Enums;

/**
 * Lifecycle of a guest's special request.
 *
 * Tamu mengirim → Menunggu. Staf lantai (pelayan, resepsionis, kasir — siapa
 * pun yang memegang `special_request.update`) bisa menandainya "Sedang
 * ditangani" lalu "Selesai", atau langsung "Selesai". Tidak ada meja manajer
 * di tengahnya. "Ditolak" selalu disertai catatan untuk tamu.
 *
 * `Approved` tersisa dari alur persetujuan manajer yang lama: tidak dibuat
 * lagi, tapi baris lamanya masih ada dan diperlakukan seperti Menunggu.
 * Kasusnya tetap di sini karena kolomnya dijaga CHECK constraint Postgres —
 * kelima nilai harus sama persis dengan database.
 */
enum SpecialRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Assigned = 'assigned';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Pending, self::Approved => 'Menunggu',
            self::Assigned => 'Sedang ditangani',
            self::Rejected => 'Ditolak',
            self::Done => 'Selesai',
        };
    }

    /** daisyUI semantic token — see docs/DAISYUI-BLUEPRINT.md. */
    public function color(): string
    {
        return match ($this) {
            self::Pending, self::Approved => 'warning',
            self::Assigned => 'info',
            self::Rejected => 'error',
            self::Done => 'success',
        };
    }

    /** Masih perlu tindakan staf — belum selesai atau ditolak. */
    public function isOpen(): bool
    {
        return ! in_array($this, [self::Rejected, self::Done], true);
    }

    /** Belum disentuh staf sama sekali. */
    public function isWaiting(): bool
    {
        return in_array($this, [self::Pending, self::Approved], true);
    }

    /**
     * @return array<int, string>
     */
    public static function openValues(): array
    {
        return array_values(array_map(
            fn (self $status) => $status->value,
            array_filter(self::cases(), fn (self $status) => $status->isOpen()),
        ));
    }

    /**
     * @return array<int, string>
     */
    public static function closedValues(): array
    {
        return [self::Done->value, self::Rejected->value];
    }

    /**
     * Langkah yang sah. Selesai dan Ditolak adalah akhir — dua pelayan yang
     * menekan tombol bersamaan tidak bisa membuka ulang permintaan yang sama.
     */
    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Pending, self::Approved => in_array($next, [self::Assigned, self::Done, self::Rejected], true),
            self::Assigned => in_array($next, [self::Done, self::Rejected], true),
            self::Done, self::Rejected => false,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
