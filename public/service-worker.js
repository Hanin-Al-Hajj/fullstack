// Keeps the app installable and shows a friendly page when the phone is offline.
// Pages, prices, stock and the cart are NEVER cached: they always come from the server.
const CACHE = 'moussawi-v1';
const PRECACHE = ['/offline.html', '/template/logo1.png', '/icons/icon-192.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((c) => c.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    if (url.origin !== location.origin) return;

    // Opening a page: network only; the offline page if the network fails.
    if (req.mode === 'navigate') {
        event.respondWith(fetch(req).catch(() => caches.match('/offline.html')));
        return;
    }

    // Template files and icons rarely change: serve from cache, refresh in the background.
    if (url.pathname.startsWith('/template/') || url.pathname.startsWith('/icons/')) {
        event.respondWith(
            caches.open(CACHE).then(async (cache) => {
                const cached = await cache.match(req);
                const network = fetch(req)
                    .then((res) => { if (res.ok) cache.put(req, res.clone()); return res; })
                    .catch(() => cached);
                return cached || network;
            }),
        );
    }
});
