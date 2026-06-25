// CR Cafe & Resto — service worker (PWA, Fase 7)
const CACHE = 'cr-resto-v1';
const PRECACHE = ['/offline.html', '/manifest.webmanifest', '/assets/logo-cr-mark.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))).then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Only handle GET; let the network deal with POST/PATCH/etc.
    if (request.method !== 'GET') {
        return;
    }

    // Navigations: network-first, fall back to the offline page when down.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match('/offline.html')));
        return;
    }

    // Static assets: cache-first with background refresh.
    if (/\.(?:css|js|png|jpe?g|svg|webp|woff2?)$/.test(new URL(request.url).pathname)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                const network = fetch(request)
                    .then((response) => {
                        const copy = response.clone();
                        caches.open(CACHE).then((cache) => cache.put(request, copy));
                        return response;
                    })
                    .catch(() => cached);

                return cached || network;
            }),
        );
    }
});
