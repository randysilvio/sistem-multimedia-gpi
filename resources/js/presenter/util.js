export const STAGE_W = 1920;
export const STAGE_H = 1080;

/** Escape HTML supaya isi slide tidak bisa menyisipkan tag/script. */
export function esc(value) {
    return String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

export function textToHtml(value) {
    return esc(String(value ?? '').trim()).replace(/\r?\n/g, '<br>');
}

/**
 * Ubah batas fisik (cm) menjadi piksel pada kanvas 1920x1080.
 * Contoh TV 55": 20cm dari 68,5cm tinggi -> 315px; 5cm dari 121,8cm lebar -> 79px.
 */
export function computeLayout(settings) {
    const s = settings.screen;
    const pxX = STAGE_W / s.widthCm;
    const pxY = STAGE_H / s.heightCm;
    return {
        top: Math.round(s.marginTopCm * pxY),
        bottom: Math.round(s.marginBottomCm * pxY),
        side: Math.round(s.marginSideCm * pxX),
    };
}

/** Ukuran layar 16:9 (cm) dari diagonal (inci). */
export function screenFromDiagonal(inches) {
    const diag = inches * 2.54;
    const k = Math.sqrt(16 * 16 + 9 * 9);
    return { widthCm: Math.round((diag * 16 / k) * 10) / 10, heightCm: Math.round((diag * 9 / k) * 10) / 10 };
}

export const SCREEN_PRESETS = [43, 50, 55, 65, 75, 85, 100];

export function hexToRgb(hex) {
    const m = /^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i.exec(hex || '');
    return m ? [parseInt(m[1], 16), parseInt(m[2], 16), parseInt(m[3], 16)] : [0, 0, 0];
}

export function getPath(obj, path) {
    return path.split('.').reduce((o, k) => (o == null ? undefined : o[k]), obj);
}

export function setPath(obj, path, value) {
    const keys = path.split('.');
    let o = obj;
    keys.slice(0, -1).forEach((k) => { if (typeof o[k] !== 'object' || o[k] === null) o[k] = {}; o = o[k]; });
    o[keys[keys.length - 1]] = value;
}

export function debounce(fn, ms) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
}

export function safeStorage() {
    try {
        const k = '__gpi_test__';
        window.localStorage.setItem(k, '1');
        window.localStorage.removeItem(k);
        return window.localStorage;
    } catch {
        return null;
    }
}

/**
 * Langkah ke slide berikutnya/sebelumnya yang TIDAK disembunyikan (seperti "Hide Slide" PowerPoint).
 * Bila tidak ada lagi slide yang tampil ke arah itu, tetap di `from`.
 */
export function stepVisible(slides, hidden, from, dir) {
    const skip = new Set(Array.isArray(hidden) ? hidden : []);
    for (let j = from + dir; j >= 0 && j < slides.length; j += dir) {
        if (!skip.has(slides[j]?.key)) return j;
    }
    return from;
}
