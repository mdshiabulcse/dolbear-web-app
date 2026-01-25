var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    '/frontend/css/app.css',
    '/frontend/js/app.js',
    '/images/ico/favicon-72x72.png',
    '/images/ico/favicon-96x96.png',
    '/images/ico/favicon-128x128.png',
    '/images/ico/favicon-144x144.png',
    '/images/ico/favicon-152x152.png',
    '/images/ico/favicon-192x192.png',
    '/images/ico/favicon-384x384.png',
    '/images/ico/favicon-512x512.png',
];

// Cache on install
self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache.map(url => {
                    // Add request with error handling
                    return new Request(url, { cache: 'reload' });
                })).catch(error => {
                    console.log('Service Worker: Cache addAll failed', error);
                    // Continue even if some files fail to cache
                    return Promise.resolve();
                });
            })
    )
});

// Clear cache on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pwa-")))
                    .filter(cacheName => (cacheName !== staticCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('offline');
            })
    )
});
