<?php

namespace App\Http\Controllers;

use App\Domains\System\Services\BusinessProfile;
use Illuminate\Http\JsonResponse;

/**
 * The web app manifest, built from the business profile.
 *
 * The install prompt and the home-screen label show the business name, so the
 * manifest has to follow Pengaturan Aplikasi like every other screen. It used
 * to be a static `public/manifest.webmanifest`, which the web server answered
 * before Laravel ever saw the request — that file is gone so this route can
 * serve the same URL the service worker already precaches.
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
                'background_color' => '#ffffff',
                'theme_color' => '#065f46',
                'icons' => [
                    [
                        'src' => '/assets/logo-cr-mark.png',
                        'sizes' => '192x192',
                        'type' => 'image/png',
                        'purpose' => 'any maskable',
                    ],
                    [
                        'src' => '/assets/logo-cr-cafe-resto.png',
                        'sizes' => '512x512',
                        'type' => 'image/png',
                        'purpose' => 'any',
                    ],
                ],
            ], options: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            ->header('Content-Type', 'application/manifest+json');
    }
}
