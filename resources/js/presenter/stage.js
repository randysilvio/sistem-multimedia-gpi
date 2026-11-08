import { STAGE_W, STAGE_H, esc, textToHtml, computeLayout, hexToRgb } from './util.js';
import { applyBackground } from './backgrounds.js';

/** Batas ukuran huruf otomatis (px pada kanvas 1920x1080) per jenis slide. */
const FIT_LIMITS = {
    text: [28, 120],
    custom: [28, 120],
    song_lyric: [28, 100],
    instruksi: [36, 150],
    bible: [30, 96],
    bible_ref: [40, 150],
    song_cover: [30, 120],
    cover: [30, 110],
    closing: [40, 150],
    end: [30, 60],
};
const CAMERA_MAX_FONT = 84;

/** Slide nyanyian: lirik lagu, sampul lagu, atau respons yang dinyanyikan jemaat. */
export function isSongSlide(slide) {
    return !!slide && (['song_cover', 'song_lyric'].includes(slide.type) || !!slide.sung);
}
const NO_HEADER_TYPES = ['cover', 'closing', 'end'];
const FULLSCREEN_TYPES = ['announcements_slideshow'];

const fitCache = new Map();
const registry = new Set();

if (document.fonts) {
    const refitAll = () => { fitCache.clear(); registry.forEach((s) => s.refit()); };
    document.fonts.ready.then(refitAll);
    document.fonts.addEventListener?.('loadingdone', refitAll);
}

export function isCameraSlide(slide) {
    return !!slide && !!slide.use_camera && !['cover', 'closing', 'announcements_slideshow'].includes(slide.type);
}

/**
 * Satu kanvas slide 1920x1080 yang di-scale ke ukuran wadahnya.
 * Dipakai oleh layar live, pratinjau "saat ini/berikutnya", dan thumbnail.
 */
export class Stage {
    /**
     * @param {HTMLElement} viewport
     * @param {{animated?: boolean, camera?: 'placeholder'|'live', logoUrl?: string, guides?: boolean}} opts
     *        guides=false: garis batas tidak pernah tampil (thumbnail kecil)
     */
    constructor(viewport, { animated = true, camera = 'placeholder', logoUrl = '', guides = true } = {}) {
        this.viewport = viewport;
        this.animated = animated;
        this.allowGuides = guides;
        this.logoUrl = logoUrl;
        this.slide = undefined;
        this.content = null;
        this.wartaTimer = null;
        this.fontSize = null;
        this.fontManual = false;
        this.needsFit = false;

        viewport.classList.add('pv-viewport');
        viewport.innerHTML = '<div class="pv-stage"><div class="pv-bg"></div><div class="pv-cam"></div>'
            + '<div class="pv-layer"></div><div class="pv-ticker"><span></span></div><div class="pv-guides"></div><div class="pv-blank"></div></div>';
        this.stage = viewport.firstElementChild;
        [this.bg, this.cam, this.layer, this.ticker, this.guides] = this.stage.children;
        if (camera === 'placeholder') {
            this.cam.innerHTML = '<div class="pv-cam-placeholder">KAMERA LIVE</div>';
        }

        this.resizeObserver = new ResizeObserver(() => this.rescale());
        this.resizeObserver.observe(viewport);
        this.rescale();
        registry.add(this);
    }

    destroy() {
        clearTimeout(this.wartaTimer);
        this.resizeObserver.disconnect();
        registry.delete(this);
    }

    rescale() {
        const w = this.viewport.clientWidth;
        const h = this.viewport.clientHeight;
        if (!w || !h) return;
        const s = Math.min(w / STAGE_W, h / STAGE_H);
        this.stage.style.transform = `translate(${(w - STAGE_W * s) / 2}px, ${(h - STAGE_H * s) / 2}px) scale(${s})`;
        if (this.needsFit) this.refit();
    }

    attachVideo(video) {
        this.cam.innerHTML = '';
        this.cam.appendChild(video);
    }

    setSettings(settings, rerender = true) {
        this.settings = settings;
        this.layout = computeLayout(settings);
        const d = settings.design;
        const st = this.stage.style;
        st.setProperty('--c1', d.color1);
        st.setProperty('--c2', d.color2);
        st.setProperty('--c3', d.color3);
        st.setProperty('--speed', d.speed);
        st.setProperty('--intensity', d.intensity);
        st.setProperty('--pv-text', d.textColor);
        st.setProperty('--pv-font', `'${d.font}'`);
        st.setProperty('--pv-song', d.songColor || '#ffe08a');
        const [r, g, b] = hexToRgb(d.shadowColor);
        st.setProperty('--pv-shadow', `rgba(${r}, ${g}, ${b}, ${d.shadowStrength})`);
        applyBackground(this.bg, d, this.animated);
        this.stage.classList.toggle('show-guides', this.allowGuides && !!settings.guides);
        this.renderGuides();
        if (rerender && this.slide !== undefined) this.show(this.slide);
    }

    renderGuides() {
        const { top, bottom, side } = this.layout;
        const sc = this.settings.screen;
        this.guides.innerHTML = `
            <div class="pv-guide-box" style="left:${side}px;right:${side}px;top:${top}px;bottom:${bottom}px"></div>
            <div class="pv-guide-label" style="right:${side + 16}px;top:${Math.max(8, top - 52)}px">↑ Area atas ${sc.marginTopCm} cm</div>
            <div class="pv-guide-label" style="left:${side + 16}px;top:${top + 14}px">Kotak tulisan</div>
            <div class="pv-guide-label" style="right:${side + 16}px;bottom:${bottom + 14}px">Kiri/kanan ${sc.marginSideCm} cm · bawah ${sc.marginBottomCm} cm</div>`;
    }

    setBlank(mode) {
        this.stage.classList.toggle('is-black', mode === 'black');
        this.stage.classList.toggle('is-white', mode === 'white');
    }

    /** Teks cepat (pengumuman singkat) di bawah layar, tanpa mengganti slide. null = sembunyikan. */
    setTicker(text) {
        const t = String(text ?? '').trim();
        this.ticker.firstElementChild.textContent = t;
        this.stage.classList.toggle('has-ticker', t !== '');
    }

    setTextHidden(hidden) {
        this.stage.classList.toggle('is-text-hidden', !!hidden);
    }

    /** Tampilkan slide. null = slide "SELESAI" (setelah slide terakhir). */
    show(slide, { transition = false } = {}) {
        if (!this.settings) return null;
        this.slide = slide;
        clearTimeout(this.wartaTimer);

        const el = this.build(slide);
        this.stage.classList.toggle('is-camera', isCameraSlide(slide));

        const old = this.content;
        if (transition && old) {
            el.classList.add('is-entering');
            this.layer.appendChild(el);
            this.fit(el, slide);
            old.classList.add('is-leaving');
            const reveal = () => el.classList.remove('is-entering');
            requestAnimationFrame(() => requestAnimationFrame(reveal));
            setTimeout(reveal, 60); // cadangan bila jendela tertutup jendela lain (rAF dijeda browser)
            setTimeout(() => old.remove(), 380);
        } else {
            this.layer.replaceChildren(el);
            this.fit(el, slide);
        }
        this.content = el;
        this.startWarta(el);
        return this.fontSize;
    }

    refit() {
        if (this.content && this.slide !== undefined) this.fit(this.content, this.slide);
    }

    build(slide) {
        const el = document.createElement('div');
        el.className = 'pv-content';
        const type = slide ? slide.type : 'end';

        if (FULLSCREEN_TYPES.includes(type)) {
            el.innerHTML = '<div class="pv-warta">' + (slide.images || []).map((img, i) => `
                <div class="pv-warta-item${i === 0 ? ' is-on' : ''}" data-duration="${Number(img.duration) || 5}">
                    <img src="${esc(img.image_url)}" alt="" decoding="async">
                    ${img.caption ? `<div class="pv-warta-caption">${esc(img.caption)}</div>` : ''}
                </div>`).join('') + '</div>';
            return el;
        }

        const { top, bottom, side } = this.layout;
        const header = this.settings.header;
        const camera = isCameraSlide(slide);
        // Pembeda nyanyian: lirik berwarna lagu (bisa dimatikan di tab Lagu)
        const song = isSongSlide(slide) && this.settings.design.songMark !== false;
        el.classList.toggle('is-song', song);
        // Gaya slide lirik (tab Lagu): nomor | terang | klasik | kiri
        const songStyle = ['song_lyric', 'song_cover'].includes(type) ? (this.settings.design.songStyle || 'nomor') : null;
        if (songStyle) el.classList.add(`ss-${songStyle}`);
        const verseLabel = type === 'song_lyric' ? (slide.is_reff ? 'Reff' : (slide.watermark ? String(slide.watermark) : '')) : '';
        // Slide lirik (bait/reff) tampil penuh tanpa judul & logo di atas, seperti slide lagu biasa
        const lyric = type === 'song_lyric';
        const coverLogo = type === 'cover' && header.logo && this.logoUrl;
        let html = '';

        // ---- Area atas (judul + logo) ----
        if (top >= 60 && !lyric) {
            const logo = header.logo && this.logoUrl ? `<img class="pv-logo" src="${esc(this.logoUrl)}" alt="" onerror="this.style.display='none'">` : '';
            if (NO_HEADER_TYPES.includes(type)) {
                // Sampul pembukaan: logo besar ada di tengah layar, tidak diulang di atas
                if (logo && !coverLogo) html += `<div class="pv-header is-centered" style="left:${side}px;right:${side}px;top:0;height:${top}px">${logo}</div>`;
            } else {
                let text = '';
                if (header.show) {
                    const title = ['song_cover', 'bible_ref'].includes(type) ? '' : (slide.title || '');
                    let chip = '';
                    if (type === 'bible' && slide.ref) chip = `<span class="pv-header-chip is-ref">${esc(slide.ref)}</span>`;
                    else if (slide.speaker) chip = `<span class="pv-header-chip ${song ? 'is-song' : 'is-speaker'}">${esc(slide.speaker)}</span>`;
                    if (title || chip) {
                        text = `<div class="pv-header-text">${title ? `<div class="pv-header-title">${esc(title)}</div>` : ''}${chip}</div>`;
                    }
                }
                if (text || logo) {
                    html += `<div class="pv-header" style="left:${side}px;right:${side}px;top:0;height:${top}px">${text || '<span></span>'}${logo}${text ? '<div class="pv-header-line"></div>' : ''}</div>`;
                }
            }
        }

        // ---- Kotak tulisan ----
        let boxTop = top;
        let boxBottom = bottom;
        let boxSide = side;
        if (lyric && !camera) {
            // Lirik memakai seluruh layar dengan tepi yang sama di atas & bawah
            const pad = Math.max(bottom, 60);
            boxTop = pad;
            boxBottom = pad;
            boxSide = Math.max(side, Math.round(STAGE_W * 0.08));
        }
        if (camera) {
            boxTop = Math.max(top, Math.round(STAGE_H * 0.58));
            html += `<div class="pv-band" style="top:${boxTop - 160}px"></div>`;
        }

        // Nomor bait / REFF berbayang di pojok kiri atas layar, di belakang lirik.
        // Angka dibuat besar; tulisan REFF lebih kecil agar tidak menutupi lirik.
        if (verseLabel && !camera && (songStyle === 'nomor' || songStyle === 'terang')) {
            const isWord = !/^\d+$/.test(verseLabel);
            const markSize = Math.round(STAGE_H * (isWord ? 0.25 : 0.44));
            const inset = Math.max(side, 70);
            html += `<div class="pv-mark${isWord ? ' is-word' : ''}" aria-hidden="true" style="left:${inset}px;top:${Math.round(STAGE_H * (isWord ? 0.1 : 0.07))}px;font-size:${markSize}px">${esc(verseLabel.toUpperCase())}</div>`;
        }

        let inner;
        switch (type) {
            case 'instruksi':
                inner = `<div class="pv-fit is-instruksi">${textToHtml(slide.content)}</div>`;
                break;
            case 'bible_ref':
                inner = `<div class="pv-fit is-bible-ref"><div class="pv-sub">${esc(slide.title || 'Pembacaan Alkitab')}</div>`
                    + `<div class="pv-big">${esc(slide.content)}</div>`
                    + (slide.version ? `<div class="pv-meta">Alkitab Terjemahan Baru</div>` : '') + '</div>';
                break;
            case 'bible': {
                // Ayat utuh per paragraf, nomor ayat kecil berwarna; rata kiri agar enak dibaca panjang
                const verses = (slide.verses || []).map((v) => `<p class="pv-verse">${v.n ? `<sup class="pv-vn">${esc(v.n)}</sup>` : ''}${esc(v.text)}</p>`).join('');
                const ref = slide.ref && !(header.show && top >= 60) ? `<div class="pv-sub">${esc(slide.ref)}</div>` : '';
                inner = `<div class="pv-fit is-bible">${ref}${verses}</div>`;
                break;
            }
            case 'song_cover':
                inner = `<div class="pv-fit">${slide.title ? `<div class="pv-sub">${esc(slide.title)}</div>` : ''}<div class="pv-big">${esc(slide.content)}</div></div>`;
                break;
            case 'cover':
                inner = `<div class="pv-fit${coverLogo ? ' is-cover' : ''}">`
                    + (coverLogo ? `<img class="pv-cover-logo" src="${esc(this.logoUrl)}" alt="" onerror="this.remove()">` : '')
                    + `<div class="pv-big">${esc(slide.title)}</div>`
                    + `<div class="pv-meta">${esc(slide.date)}</div>`
                    + (slide.theme ? `<div class="pv-meta">TEMA: ${esc(slide.theme)}</div>` : '')
                    + (slide.preacher ? `<div class="pv-meta">PELAYAN FIRMAN: ${esc(slide.preacher)}</div>` : '')
                    + '</div>';
                break;
            case 'closing': {
                const [first, ...rest] = String(slide.content || '').split(/\r?\n/);
                inner = `<div class="pv-fit"><div class="pv-big">${esc(first)}${rest.length ? `<br><span class="pv-accent">${esc(rest.join(' '))}</span>` : ''}</div></div>`;
                break;
            }
            case 'end':
                inner = '<div class="pv-fit" style="opacity:.35">SELESAI</div>';
                break;
            default: {
                // Label pembicara (PNT/JEMAAT) tampil di area atas; bila area atas mati, di atas teks.
                const noHeader = !(header.show && top >= 60);
                let speaker = slide.speaker && noHeader ? `<div class="pv-sub">${esc(slide.speaker)}</div>` : '';
                if (type === 'song_lyric' && songStyle === 'kiri') {
                    const label = slide.is_reff ? 'Reff' : (slide.watermark ? `Bait ${esc(slide.watermark)}` : '');
                    inner = `<div class="pv-fit is-ss-kiri">${label ? `<div class="pv-ss-label">${label}</div>` : ''}${textToHtml(slide.content)}</div>`;
                    break;
                }
                if (lyric && songStyle === 'klasik') {
                    const label = slide.is_reff ? 'Reff' : (slide.watermark ? `Bait ${esc(slide.watermark)}` : '');
                    speaker = label ? `<div class="pv-sub">${label}</div>` : '';
                }
                inner = `<div class="pv-fit">${speaker}${textToHtml(slide.content)}</div>`;
            }
        }
        html += `<div class="pv-box" style="left:${boxSide}px;right:${boxSide}px;top:${boxTop}px;bottom:${boxBottom}px">${inner}</div>`;

        el.innerHTML = html;
        return el;
    }

    /** Cari ukuran huruf terbesar yang masih muat di kotak tulisan (binary search). */
    fit(el, slide) {
        const box = el.querySelector('.pv-box');
        this.fontSize = null;
        this.fontManual = false;
        if (!box) return;
        const fitEl = box.firstElementChild;
        const type = slide ? slide.type : 'end';
        const manual = slide && this.settings.fonts ? this.settings.fonts[slide.key] : null;

        if (manual) {
            fitEl.style.fontSize = manual + 'px';
            this.fontSize = manual;
            this.fontManual = true;
            this.needsFit = false;
            return;
        }

        const boxW = box.clientWidth;
        const boxH = box.clientHeight;
        if (!boxW || !boxH) { this.needsFit = true; return; }

        const camera = isCameraSlide(slide);
        const cacheKey = [slide?.key, type, slide?.title, slide?.content, camera, boxW, boxH, this.settings.design.font, this.settings.header.show, this.settings.header.logo, this.settings.design.songMark, this.settings.design.songStyle].join('|');
        let size = fitCache.get(cacheKey);

        if (size === undefined) {
            let [lo, hi] = FIT_LIMITS[type] || FIT_LIMITS.text;
            if (camera) hi = Math.min(hi, CAMERA_MAX_FONT);
            const fits = () => fitEl.offsetHeight <= boxH && fitEl.scrollWidth <= boxW + 1;
            while (lo < hi) {
                const mid = Math.ceil((lo + hi) / 2);
                fitEl.style.fontSize = mid + 'px';
                if (fits()) lo = mid; else hi = mid - 1;
            }
            size = lo;
            if (fitCache.size > 2000) fitCache.clear();
            fitCache.set(cacheKey, size);
        }

        fitEl.style.fontSize = size + 'px';
        this.fontSize = size;
        this.needsFit = false;
    }

    startWarta(el) {
        if (!this.animated) return;
        const items = el.querySelectorAll('.pv-warta-item');
        if (items.length < 2) return;
        let i = 0;
        const dur = (n) => (Number(items[n].dataset.duration) || 5) * 1000;
        const tick = () => {
            items[i].classList.remove('is-on');
            i = (i + 1) % items.length;
            items[i].classList.add('is-on');
            this.wartaTimer = setTimeout(tick, dur(i));
        };
        this.wartaTimer = setTimeout(tick, dur(0));
    }
}
