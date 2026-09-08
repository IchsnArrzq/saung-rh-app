<?php

namespace App\Http\Controllers;

use App\Domains\System\Services\BusinessProfile;
use Illuminate\Http\JsonResponse;

/**
 * The web app manifest, built from the business profile.
 *
 * The install prompt, the home-screen label and the home-screen icon all come
 * from Pengaturan Aplikasi like every other screen. It used to be a static
 * `public/manifest.webmanifest`, which the web server answered before Laravel
 * ever saw the request — that file is gone so this route can serve the same URL
 * the service worker already precaches.
 */
class PwaManifestController extends Controller
{
    public function __invoke(BusinessProfile $business): JsonResponse
    {
        return response()
            ->json([
                'name' => $business->name(),
                'short_name' => $business->shortName(),
                'description' => $business->tagline(),
                'start_url' => '/',
                'scope' => '/',
                'display' => 'standalone',
                'orientation' => 'portrait-primary',
                // Warna tema aplikasi (resources/css/app.css): latar halaman dan primary.
                'background_color' => '#eef2f7',
                'theme_color' => '#ff4f55',
                'icons' => array_values(array_filter([
                    $this->icon($business->markUrl(), $business->markPath()),
                    $this->icon($business->logoUrl(), $business->logoPath()),
                ])),
            ], options: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            ->header('Content-Type', 'application/manifest+json');
    }

    /**
     * One manifest icon, measured rather than declared.
     *
     * The sizes used to be written by hand as 192x192 and 512x512 while the
     * files were 490x499 and 538x639 — and now that an admin can upload their
     * own, no fixed number could be right anyway. An icon whose file cannot be
     * read is dropped instead of announced.
     *
     * `purpose` stays `any`: `maskable` promises the artwork keeps a safe zone
     * that Android may crop into, and nothing here can promise that about a
     * file someone just uploaded.
     *
     * @return array<string, string>|null
     */
    private function icon(string $url, string $path): ?array
    {
        $size = is_readable($path) ? @getimagesize($path) : false;

        if ($size === false) {
            return null;
        }

        return [
            'src' => $url,
            'sizes' => $size[0].'x'.$size[1],
            'type' => $size['mime'],
            'purpose' => 'any',
        ];
    }
}
