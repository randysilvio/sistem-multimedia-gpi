/**
 * Mode offline: daftarkan service worker (public/sw.js) supaya Kontrol Live & Layar Live
 * tetap terbuka dan berjalan saat internet putus, lalu laporkan status koneksi.
 */
export function setupOffline(cfg, onChange) {
    const secure = location.protocol === 'https:' || ['localhost', '127.0.0.1', '[::1]'].includes(location.hostname);
    if ('serviceWorker' in navigator && cfg.sw && secure) {
        navigator.serviceWorker.register(cfg.sw, { scope: cfg.swScope }).catch(() => { /* tanpa SW tetap jalan online */ });
    }
    const update = () => onChange?.(navigator.onLine);
    window.addEventListener('online', update);
    window.addEventListener('offline', update);
    update();
}
