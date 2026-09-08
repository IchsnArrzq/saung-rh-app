<?php

namespace App\Domains\System\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * The uploaded files behind BusinessProfile's logo and mark.
 *
 * The two images used to be packaged assets — `public/assets/logo-cr-*.png`
 * spelled out in half a dozen layouts — so a restaurant with its own logo had
 * to replace files on the server. They are settings now, like the name and the
 * phone number beside them on Pengaturan Aplikasi.
 *
 * A Service rather than a UseCase for the same reason AppSettings is one: it
 * moves a file and writes a configuration row, and carries no business rule
 * (AGENTS.md § Configuration).
 */
class BrandAssets
{
    /** Slot => settings key. Two slots, fixed: a wordmark and a square mark. */
    private const SLOTS = [
        'logo' => BusinessProfile::LOGO_KEY,
        'mark' => BusinessProfile::MARK_KEY,
    ];

    private const FOLDER = 'brand';

    public function __construct(private readonly AppSettings $settings) {}

    /**
     * @return array<int, string>
     */
    public static function slots(): array
    {
        return array_keys(self::SLOTS);
    }

    public static function keyFor(string $slot): string
    {
        return self::SLOTS[$slot]
            ?? throw new InvalidArgumentException("Slot logo tidak dikenal: {$slot}.");
    }

    /**
     * Replace one slot's image. The previous file is removed in the same call:
     * a slot holds exactly one image, so keeping the old one would only grow
     * the storage folder with files nothing can ever reach again.
     */
    public function store(string $slot, UploadedFile $file): void
    {
        $key = self::keyFor($slot);

        $this->deleteFile($key);

        $path = $file->store(self::FOLDER, BusinessProfile::DISK);

        $this->settings->set($key, $path, group: 'brand', type: 'image');
    }

    /**
     * Empty the slot and fall back to the packaged image (BusinessProfile).
     */
    public function clear(string $slot): void
    {
        $key = self::keyFor($slot);

        $this->deleteFile($key);

        $this->settings->set($key, '', group: 'brand', type: 'image');
    }

    private function deleteFile(string $key): void
    {
        $path = trim((string) $this->settings->get($key, ''));

        if ($path !== '') {
            Storage::disk(BusinessProfile::DISK)->delete($path);
        }
    }
}
