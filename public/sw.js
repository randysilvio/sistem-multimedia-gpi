/*
 * Service worker Sistem Multimedia GPI — supaya tayangan tetap jalan saat internet putus.
 *
 * - Aset tampilan (/build, gambar, logo, warta, font): disimpan sekali, dipakai dari cache.
 * - Halaman Kontrol Live & Layar Live + data slide: selalu ambil yang terbaru dari
 *   server; bila server tak terjangkau, pakai salinan terakhir.
 * - Permintaan lain (simpan, login, status 2 komputer) tidak disentuh.
 *
 * Kontrol Live dan Layar Live di SATU komputer saling terhubung tanpa internet
 * (BroadcastChannel), jadi ibadah tetap bisa ditayangkan sampai selesai.
 */
const VERSION = 'gpi-v1';
const STATIC_CACHE = `${VERSION}-static`;
const PAGE_CACHE = `${VERSION}-pages`;

self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(keys.filter((k) => !k.startsWith(VERSION)).map((k) => caches.delete(k)));
        await self.clients.claim();
    })());
});

const isVideo = (url) => /\.(mp4|webm|mov)$/i.test(url.pathname); // video diminta per potongan: biarkan browser
const isStatic = (url) => !isVideo(url) && (/\/(build|images|fonts)\//.test(url.pathname)
    || /\/storage\/(branding|sinode)\//.test(url.pathname)
    || /\.(woff2?|png|jpe?g|webp|svg|gif|ico)$/i.test(url.pathname));

const isPresenterPage = (url) => /\/liturgi\/(kontrol|output)\/\d+$/.test(url.pathname);
const isSlidesData = (url) => /\/liturgi\/kontrol\/\d+\/slides$/.test(url.pathname);

async function cacheFirst(request) {
    const cache = await caches.open(STATIC_CACHE);
    const hit = await cache.match(request);
    if (hit) return hit;
    const res = await fetch(request);
    if (res.ok && res.type === 'basic') cache.put(request, res.clone());
    return res;
}

async function networkFirst(request) {
    const cache = await caches.open(PAGE_CACHE);
    try {
        const res = await fetch(request);
        // Hanya simpan halaman yang benar-benar terbuka (bukan alihan ke login)
        if (res.ok && !res.redirected) cache.put(request, res.clone());
        return res;
    } catch (err) {
        const hit = await cache.match(request, { ignoreSearch: true });
        if (hit) return hit;
        throw err;
    }
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    if (isStatic(url)) {
        event.respondWith(cacheFirst(request));
    } else if (isPresenterPage(url) || isSlidesData(url)) {
        event.respondWith(networkFirst(request));
    }
});

// Saat keluar (logout) halaman: hapus salinan halaman yang perlu login
self.addEventListener('message', (event) => {
    if (event.data === 'clear-pages') event.waitUntil(caches.delete(PAGE_CACHE));
});
