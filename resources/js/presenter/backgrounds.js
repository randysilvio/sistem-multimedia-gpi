/**
 * Latar animasi ringan. Struktur DOM dibuat sekali per jenis latar;
 * perubahan warna/kecepatan hanya mengganti CSS variable (tanpa membangun ulang),
 * sehingga animasi tetap mulus dan tidak restart saat slide berganti.
 */
export const BACKGROUNDS = [
    { id: 'static', name: 'Gradien Statis' },
    { id: 'aurora', name: 'Aurora' },
    { id: 'flow', name: 'Gradien Mengalir' },
    { id: 'bokeh', name: 'Cahaya Bokeh' },
    { id: 'waves', name: 'Gelombang' },
    { id: 'rays', name: 'Sinar Berputar' },
    { id: 'grid', name: 'Grid Bergerak' },
    { id: 'video', name: 'Video' },
];

// Posisi tetap (bukan acak) supaya pratinjau dan layar live identik.
const DOTS = [
    [4, 180, 30, 0, 60, 1], [12, 90, 24, 9, -40, 2], [19, 260, 38, 4, 80, 1], [27, 120, 27, 15, 30, 2],
    [35, 200, 34, 21, -70, 1], [43, 70, 22, 6, 20, 2], [51, 240, 40, 12, -50, 1], [58, 110, 26, 25, 60, 2],
    [66, 170, 31, 2, -30, 1], [73, 80, 23, 18, 40, 2], [80, 220, 36, 8, -60, 1], [88, 130, 28, 28, 50, 2],
    [94, 190, 33, 14, -20, 1], [8, 60, 21, 31, 10, 2],
];

const WAVE_PATH = 'M0,210 C320,110 640,310 960,210 C1280,110 1600,310 1920,210 C2240,110 2560,310 2880,210 C3200,110 3520,310 3840,210 L3840,420 L0,420 Z';

function markup(bg) {
    switch (bg) {
        case 'aurora':
            return '<div class="pv-blob b1"></div><div class="pv-blob b2"></div><div class="pv-blob b3"></div>';
        case 'flow':
            return '<div class="pv-flow"></div>';
        case 'bokeh':
            return '<div class="pv-dots">' + DOTS.map(([x, s, d, o, dx, c]) =>
                `<span class="pv-dot" style="--x:${x}%;--s:${s}px;--d:${d}s;--o:${o};--dx:${dx}px;--dc:var(--c${c})"></span>`).join('') + '</div>';
        case 'waves':
            return [1, 2, 3].map((n) => `<div class="pv-wave w${n}"><svg viewBox="0 0 3840 420" preserveAspectRatio="none"><path fill="currentColor" d="${WAVE_PATH}"/></svg></div>`).join('');
        case 'rays':
            return '<div class="pv-rays"></div>';
        case 'grid':
            return '<div class="pv-grid"></div>';
        default:
            return '';
    }
}

/**
 * @param {HTMLElement} el  elemen .pv-bg
 * @param {object} design   settings.design
 * @param {boolean} animated false = beku (untuk thumbnail / hemat daya)
 */
export function applyBackground(el, design, animated = true) {
    const videoUrl = window.PRESENTER?.videoUrl;
    let bg = BACKGROUNDS.some((b) => b.id === design.bg) ? design.bg : 'static';
    if (bg === 'video' && !videoUrl) bg = 'static';

    if (bg === 'video') {
        // Video hanya diputar di Layar Live; pratinjau/thumbnail cukup bingkai pertama (ringan)
        const play = animated && !design.powerSave && document.body.hasAttribute('data-live');
        const key = `video:${play}`;
        if (el.dataset.bg !== key) {
            el.innerHTML = `<video class="pv-video" src="${videoUrl}" muted loop playsinline preload="${play ? 'auto' : 'metadata'}"${play ? ' autoplay' : ''} style="opacity:0" onloadeddata="this.style.opacity=1" onerror="this.remove()"></video><div class="pv-video-shade"></div>`;
            el.dataset.bg = key;
            if (play) el.querySelector('video').play?.().catch(() => {});
        }
        el.className = 'pv-bg pv-bg--video';
        return;
    }
    if (el.dataset.bg !== bg) {
        el.innerHTML = markup(bg);
        el.dataset.bg = bg;
    }
    el.className = `pv-bg pv-bg--${bg}` + (animated && !design.powerSave ? '' : ' is-paused');
}
