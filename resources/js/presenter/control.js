import { Stage, isCameraSlide } from './stage.js';
import { applyBackground } from './backgrounds.js';
import { createChannel, pushServerState, pollServerState, jsonHeaders } from './sync.js';
import qrcode from 'qrcode-generator';
import { initEditor } from './editor.js';
import { setupOffline } from './offline.js';
import { computeLayout, debounce, getPath, setPath, safeStorage, screenFromDiagonal, stepVisible, SCREEN_PRESETS } from './util.js';

/*
 * Control panel bergaya PowerPoint:
 *  - MODE PERSIAPAN (seperti "Normal view"): klik thumbnail hanya MEMILIH slide untuk dilihat/diedit.
 *    Layar live tetap menampilkan sampul (siaga).
 *  - MODE TAYANG (seperti "Presenter View"): dimulai dengan F5 (dari awal) / Shift+F5 (dari slide
 *    yang dipilih). Slide besar = yang tampil di layar jemaat, kanan = slide berikutnya + catatan.
 *    Klik satu kali di daftar TIDAK mengganti layar live: hanya menyiapkan slide berikutnya;
 *    → / Spasi atau klik dua kali untuk menayangkan.
 */

const cfg = window.PRESENTER;
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];
const store = safeStorage();
const indexKey = `gpi:${cfg.scheduleId}:index`;        // slide di layar live (dibaca juga oleh layar live)
const modeKey = `gpi:${cfg.scheduleId}:presenting`;
const selKey = `gpi:${cfg.scheduleId}:sel`;
const remoteKey = `gpi:${cfg.scheduleId}:remote`;
const read = (k) => { try { return store?.getItem(k) ?? null; } catch { return null; } };
const write = (k, v) => { try { store?.setItem(k, String(v)); } catch { /* abaikan */ } };

const S = {
    slides: cfg.slides,
    settings: cfg.settings,
    presenting: read(modeKey) === '1',
    live: 0,      // slide yang tampil di layar jemaat
    sel: 0,       // slide yang dipilih di mode persiapan
    cue: null,    // mode tayang: slide yang disiapkan sebagai "berikutnya"
    blank: null,
    hideText: false,
    ticker: null, // teks cepat di bawah layar live
};
const clientId = Math.random().toString(36).slice(2, 10);
const clamp = (i) => Math.max(0, Math.min(S.slides.length - 1, Number.isFinite(i) ? i : 0));
const standbyIndex = () => { const i = S.slides.findIndex((s) => s.type === 'cover'); return i >= 0 ? i : 0; };
const isHidden = (i) => (S.settings.hidden || []).includes(S.slides[i]?.key);
const step = (from, dir) => stepVisible(S.slides, S.settings.hidden, from, dir);
const firstVisible = () => (isHidden(0) ? step(0, 1) : 0);
const lastVisible = () => { const l = S.slides.length - 1; return isHidden(l) ? step(l, -1) : l; };
const nextLive = () => (S.cue ?? step(S.live, 1));
const mainIndex = () => (S.presenting ? S.live : S.sel);
const notesMap = () => (S.settings.notes && !Array.isArray(S.settings.notes) ? S.settings.notes : {});

S.sel = clamp(Number(read(selKey) ?? read(indexKey)));
S.live = S.presenting ? clamp(Number(read(indexKey))) : standbyIndex();

const stageOpts = { logoUrl: cfg.logoUrl };
const current = new Stage($('#stage-current'), { ...stageOpts, animated: true });
const next = new Stage($('#stage-next'), { ...stageOpts, animated: false, guides: false });
const thumbStages = new Map();
let gridStages = [];
let sectionStarts = [];
let shownMain = null;

// =============================================================================
// Util UI
// =============================================================================
let toastTimer;
function toast(msg) {
    const t = $('#toast');
    t.textContent = msg;
    t.classList.add('is-on');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove('is-on'), 2600);
}

function slideSummary(slide) {
    if (!slide) return 'Akhir tayangan';
    switch (slide.type) {
        case 'announcements_slideshow': return `Warta sinode (${(slide.images || []).length} gambar)`;
        case 'cover': return [slide.title, slide.date, slide.theme && `Tema: ${slide.theme}`].filter(Boolean).join('\n');
        case 'song_cover': return [slide.title, slide.content].filter(Boolean).join('\n');
        default: return [slide.title, slide.speaker && `[${slide.speaker}]`, slide.content].filter(Boolean).join('\n');
    }
}

function setPressed(cmd, on) {
    $$(`[data-cmd="${cmd}"]`).forEach((b) => b.setAttribute('aria-pressed', on ? 'true' : 'false'));
}

// =============================================================================
// Sinkronisasi ke layar live
// =============================================================================
let projectorWindow = null;
let projectorSeen = 0;

const channel = createChannel(cfg.scheduleId, (msg) => {
    if (!msg || typeof msg !== 'object') return;
    if (['hello', 'pong', 'state'].includes(msg.type)) projectorSeen = Date.now();
    if (msg.type === 'hello') {
        channel.send({ type: 'slides', slides: S.slides, settings: S.settings });
        sendState();
    }
    if (msg.type === 'state' && msg.from === 'projector') {
        // Tombol ditekan di jendela layar live: ikuti, dan anggap sedang tayang
        S.blank = msg.blank ?? null;
        S.hideText = !!msg.hideText;
        if (msg.ticker !== undefined) S.ticker = msg.ticker || null;
        if (!S.presenting) setMode(true);
        setLive(Number(msg.index), { broadcast: false });
    }
    updateProjectorStatus();
});

setInterval(() => { channel.send({ type: 'ping' }); updateProjectorStatus(); }, 4000);

const projectorConnected = () => Date.now() - projectorSeen < 9500;

function updateProjectorStatus() {
    const el = $('#projector-status');
    const live = projectorConnected();
    el.textContent = live ? 'Layar live: terhubung' : (projectorWindow && !projectorWindow.closed ? 'Layar live: memuat…' : 'Layar live: belum dibuka');
    el.classList.toggle('is-live', live);
}

const statePayload = () => ({ index: S.live, key: S.slides[S.live]?.key ?? null, blank: S.blank, hideText: S.hideText, ticker: S.ticker, client: clientId });

const pushToServer = debounce(() => {
    pushServerState(cfg.routes.state, csrf, statePayload()).then((ok) => {
        $('#status-sync').textContent = ok ? 'Sinkron: 1 PC + jaringan' : 'Sinkron: 1 PC (server tidak terjangkau)';
        $('#status-sync').classList.toggle('is-warn', !ok);
    });
}, 120);

function sendState() {
    channel.send({ type: 'state', from: 'control', ...statePayload() });
    pushToServer();
}

async function openProjector() {
    let features = 'popup=yes,menubar=no,toolbar=no,location=no,status=no';
    try {
        if ('getScreenDetails' in window) {
            const details = await window.getScreenDetails();
            const target = details.screens.find((s) => s !== details.currentScreen) || details.currentScreen;
            features += `,left=${target.availLeft},top=${target.availTop},width=${target.availWidth},height=${target.availHeight}`;
        } else {
            features += `,left=0,top=0,width=${screen.availWidth},height=${screen.availHeight}`;
        }
    } catch { /* izin multi-layar ditolak: buka di layar ini */ }
    projectorWindow = window.open(cfg.routes.projector, `gpi-live-${cfg.scheduleId}`, features);
    if (!projectorWindow) toast('Pop-up diblokir browser. Izinkan pop-up untuk situs ini, lalu coba lagi.');
    else toast('Layar live dibuka. Seret ke TV/proyektor bila perlu, lalu klik layarnya untuk layar penuh.');
    updateProjectorStatus();
}

/** Seperti F5 PowerPoint: buka layar live bila belum ada (tidak dibuka ulang bila sudah tersambung). */
function ensureProjector() {
    if (projectorConnected() || (projectorWindow && !projectorWindow.closed) || read(remoteKey) === '1') return;
    openProjector();
}

async function copyRemoteLink() {
    const url = `${cfg.routes.projector}?remote=1`;
    write(remoteKey, '1');
    try {
        await navigator.clipboard.writeText(url);
        toast('Link layar live 2 komputer disalin. Buka di browser komputer layar (perlu login).');
    } catch {
        window.prompt('Salin link ini dan buka di komputer layar live (perlu login):', url);
    }
}

// =============================================================================
// Mode persiapan / mode tayang
// =============================================================================
let timerStart = Date.now();
let timerPausedAt = null;

function setMode(presenting) {
    S.presenting = presenting;
    write(modeKey, presenting ? '1' : '0');
    document.body.classList.toggle('is-presenting', presenting);
    if (presenting) toggleEditor(false);
}

function startShow(from) {
    let target = clamp(from);
    if (isHidden(target)) target = step(target, 1);
    setMode(true);
    timerStart = Date.now();
    timerPausedAt = null;
    $('#timer-toggle').textContent = 'Jeda';
    setLive(target);
    ensureProjector();
    toast(`Tayangan dimulai dari slide ${target + 1}.`);
}

function endShow() {
    S.sel = S.live;
    write(selKey, S.sel);
    S.blank = null;
    S.hideText = false;
    setMode(false);
    setLive(standbyIndex());
    toast('Tayangan diakhiri. Layar live menampilkan sampul.');
}

// =============================================================================
// Navigasi
// =============================================================================
function setLive(i, { broadcast = true } = {}) {
    S.live = clamp(i);
    S.cue = null;
    write(indexKey, S.live);
    render();
    if (broadcast) sendState();
}

function select(i) {
    S.sel = clamp(i);
    write(selKey, S.sel);
    render();
}

/** Mode tayang: siapkan slide sebagai "berikutnya" tanpa mengubah layar live. */
function cue(i) {
    const target = clamp(i);
    S.cue = target === S.live ? null : target;
    render();
    if (S.cue !== null) toast(`Slide ${target + 1} disiapkan. Tekan → atau Spasi untuk menayangkan.`);
}

function advance() {
    const n = nextLive();
    if (n === S.live) { toast('Ini slide terakhir.'); return; }
    setLive(n);
}

const nav = {
    first: () => (S.presenting ? setLive(firstVisible()) : select(0)),
    prev: () => (S.presenting ? setLive(step(S.live, -1)) : select(S.sel - 1)),
    next: () => (S.presenting ? advance() : select(S.sel + 1)),
    last: () => (S.presenting ? setLive(lastVisible()) : select(S.slides.length - 1)),
    to: (i) => (S.presenting ? setLive(i) : select(i)),
};

// Klik = pilih (persiapan) / siapkan berikutnya (tayang). Klik dua kali = tayangkan (hanya saat tayang).
const pick = (i) => (S.presenting ? cue(i) : select(i));
const take = (i) => { if (S.presenting) setLive(i); else select(i); };

// =============================================================================
// Tampilan
// =============================================================================
function render() {
    const mi = mainIndex();
    const slide = S.slides[mi];
    current.show(slide, { transition: shownMain !== null && shownMain !== mi && S.presenting });
    shownMain = mi;

    if (S.presenting) {
        const n = nextLive();
        const has = n !== S.live;
        next.show(has ? S.slides[n] : null);
        $('#side-caption').textContent = S.cue !== null ? 'Berikutnya (disiapkan)' : 'Berikutnya';
        $('#next-label').textContent = has ? `Slide ${n + 1}` : 'Akhir';
        $('#main-caption').textContent = `● Tayang: slide ${S.live + 1} dari ${S.slides.length}`;
        const note = (notesMap()[slide?.key] || '').trim();
        const nv = $('#notes-view');
        nv.textContent = note || 'Tidak ada catatan untuk slide ini.';
        nv.classList.toggle('is-empty', !note);
    } else {
        next.show(S.slides[S.live]);
        $('#side-caption').textContent = 'Layar live sekarang';
        $('#next-label').textContent = S.live === standbyIndex() ? 'Sampul (siaga)' : `Slide ${S.live + 1}`;
        $('#main-caption').textContent = `Pratinjau slide ${S.sel + 1} (belum tayang)`;
        const ni = $('#notes-input');
        if (document.activeElement !== ni) ni.value = notesMap()[slide?.key] || '';
    }

    const cueBar = $('#cue-bar');
    cueBar.hidden = !(S.presenting && S.cue !== null);
    if (!cueBar.hidden) $('#cue-text').textContent = `Berikutnya: slide ${S.cue + 1}, ${S.slides[S.cue]?.section ?? ''}`;

    $('#badge-hidden-slide').hidden = !isHidden(mi);
    setPressed('toggle-hidden', isHidden(mi));
    $('#outline-hint').textContent = S.presenting ? 'klik = siapkan · 2× = tayang' : 'klik = pilih';
    $('#grid-hint').textContent = S.presenting ? 'Klik = siapkan berikutnya · Klik dua kali = tayangkan sekarang · Esc untuk menutup' : 'Klik = pilih slide · Esc untuk menutup';
    $('#status-mode').textContent = S.presenting ? '● Mode tayang' : 'Mode persiapan';
    $('#status-mode').classList.toggle('is-live', S.presenting);

    markActive();
    updateCounters();
    updateFontUi();
    applyBlankUi();
}

function updateCounters() {
    const total = S.slides.length;
    const mi = mainIndex();
    const input = $('#slide-input');
    input.max = total;
    if (document.activeElement !== input) input.value = mi + 1;
    $('#slide-total').textContent = total;
    $('#status-slide').textContent = `Slide ${mi + 1} dari ${total}`;
    $('#status-section').textContent = S.slides[mi]?.section ?? '';
    $('#badge-cam').hidden = !isCameraSlide(S.slides[S.live]);
}

function markThumb(el, i) {
    el.classList.toggle('is-active', i === mainIndex());
    el.classList.toggle('is-live', S.presenting && i === S.live);
    el.classList.toggle('is-cue', S.presenting && i === S.cue);
    el.classList.toggle('is-hidden', isHidden(i));
}

function markActive() {
    $$('.cp-thumb').forEach((t) => markThumb(t, Number(t.dataset.index)));
    $$('.cp-grid-item').forEach((g) => markThumb(g, Number(g.dataset.index)));
    if (!S.presenting) $(`.cp-thumb[data-index="${S.sel}"]`)?.scrollIntoView({ block: 'nearest' });
    const mi = mainIndex();
    const sec = [...sectionStarts].reverse().find((s) => s.index <= mi);
    $$('#outline button').forEach((b) => {
        b.classList.toggle('is-active', !!sec && Number(b.dataset.index) === sec.index);
        b.classList.toggle('is-cue', S.presenting && S.cue !== null && Number(b.dataset.index) === S.cue);
    });
}

function applyBlankUi() {
    // Layar hitam/putih berlaku untuk layar live: tampilkan di pratinjau yang mewakili layar live
    const liveStage = S.presenting ? current : next;
    const other = S.presenting ? next : current;
    liveStage.setBlank(S.blank);
    liveStage.setTextHidden(S.hideText);
    liveStage.setTicker(S.ticker);
    other.setBlank(null);
    other.setTextHidden(false);
    other.setTicker(null);
    setPressed('ticker', !!S.ticker);
    $$('[data-badge="ticker"]').forEach((b) => { b.hidden = !S.ticker; });
    setPressed('black', S.blank === 'black');
    setPressed('white', S.blank === 'white');
    setPressed('hide-text', S.hideText);
    $$('[data-badge="black"]').forEach((b) => { b.hidden = S.blank !== 'black'; });
    $$('[data-badge="white"]').forEach((b) => { b.hidden = S.blank !== 'white'; });
    $$('[data-badge="hide"]').forEach((b) => { b.hidden = !S.hideText; });
}

function toggleBlank(mode) {
    S.blank = S.blank === mode ? null : mode;
    applyBlankUi();
    sendState();
}

function toggleHideText() {
    S.hideText = !S.hideText;
    applyBlankUi();
    sendState();
}

// ---------- Sembunyikan slide ----------
function toggleHidden(i = mainIndex()) {
    const key = S.slides[i]?.key;
    if (!key) return;
    const set = new Set(S.settings.hidden || []);
    const hide = !set.has(key);
    if (hide) set.add(key); else set.delete(key);
    S.settings.hidden = [...set];
    if (S.cue === i && hide) S.cue = null;
    applySettings();
    render();
    toast(hide ? `Slide ${i + 1} disembunyikan dan dilewati saat tayang.` : `Slide ${i + 1} ditampilkan lagi.`);
}

// ---------- Catatan operator ----------
$('#notes-input').addEventListener('input', (e) => {
    const key = S.slides[S.sel]?.key;
    if (!key) return;
    const notes = { ...notesMap() };
    const v = e.target.value;
    if (v.trim()) notes[key] = v; else delete notes[key];
    S.settings.notes = notes;
    setSaveStatus('pending');
    saveSettings();
});

// ---------- Thumbnail kiri (dibuat saat terlihat saja = ringan) ----------
const thumbObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const i = Number(entry.target.dataset.index);
        if (!thumbStages.has(i) && S.slides[i]) {
            const st = new Stage(entry.target.querySelector('.cp-thumb-stage'), { ...stageOpts, animated: false, guides: false });
            st.setSettings(S.settings, false);
            st.show(S.slides[i]);
            thumbStages.set(i, st);
        }
        thumbObserver.unobserve(entry.target);
    });
}, { root: $('#thumbs'), rootMargin: '400px 0px' });

function buildThumbs() {
    thumbObserver.disconnect();
    thumbStages.forEach((st) => st.destroy());
    thumbStages.clear();
    const pane = $('#thumbs');
    pane.replaceChildren();
    let lastSection = null;
    S.slides.forEach((slide, i) => {
        if (slide.section !== lastSection) {
            const h = document.createElement('div');
            h.className = 'cp-thumb-section';
            h.textContent = slide.section;
            pane.appendChild(h);
            lastSection = slide.section;
        }
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'cp-thumb';
        b.dataset.index = i;
        b.title = slideSummary(slide).slice(0, 160);
        b.innerHTML = `<span class="cp-thumb-num">${i + 1}</span><span class="cp-thumb-frame"><span class="cp-thumb-stage"></span>${slide.use_camera ? '<span class="cp-thumb-flag">KAMERA</span>' : ''}<span class="cp-thumb-live">LIVE</span></span>`;
        pane.appendChild(b);
        thumbObserver.observe(b);
    });
    markActive();
}

$('#thumbs').addEventListener('click', (e) => {
    const t = e.target.closest('.cp-thumb');
    if (!t) return;
    t.blur();
    pick(Number(t.dataset.index));
});
$('#thumbs').addEventListener('dblclick', (e) => {
    const t = e.target.closest('.cp-thumb');
    if (t) take(Number(t.dataset.index));
});

// ---------- Urutan ibadah ----------
function buildOutline() {
    sectionStarts = [];
    S.slides.forEach((s, i) => {
        if (i === 0 || s.section !== S.slides[i - 1].section) sectionStarts.push({ section: s.section, index: i });
    });
    const ol = $('#outline');
    ol.replaceChildren();
    sectionStarts.forEach((sec, n) => {
        const end = (sectionStarts[n + 1]?.index ?? S.slides.length) - 1;
        const li = document.createElement('li');
        const b = document.createElement('button');
        b.type = 'button';
        b.dataset.index = sec.index;
        const name = document.createElement('span');
        name.textContent = sec.section;
        const range = document.createElement('small');
        range.textContent = end > sec.index ? `${sec.index + 1}–${end + 1}` : `${sec.index + 1}`;
        b.append(name, range);
        li.appendChild(b);
        ol.appendChild(li);
    });
    markActive();
}

$('#outline').addEventListener('click', (e) => {
    const b = e.target.closest('button[data-index]');
    if (!b) return;
    b.blur();
    pick(Number(b.dataset.index));
});
$('#outline').addEventListener('dblclick', (e) => {
    const b = e.target.closest('button[data-index]');
    if (b) take(Number(b.dataset.index));
});

// ---------- Semua slide (G) ----------
function toggleGrid(force) {
    const overlay = $('#grid-overlay');
    const open = force ?? overlay.hidden;
    overlay.hidden = !open;
    setPressed('grid', open);
    gridStages.forEach((st) => st.destroy());
    gridStages = [];
    const grid = $('#grid');
    grid.replaceChildren();
    if (!open) return;

    S.slides.forEach((slide, i) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'cp-grid-item';
        b.dataset.index = i;
        b.innerHTML = `<span class="cp-grid-stage"><span class="cp-thumb-live">LIVE</span></span><span class="cp-grid-meta"><b>${i + 1}</b><span></span></span>`;
        b.querySelector('.cp-grid-meta span').textContent = slide.section;
        markThumb(b, i);
        grid.appendChild(b);
        const st = new Stage(b.querySelector('.cp-grid-stage'), { ...stageOpts, animated: false, guides: false });
        st.setSettings(S.settings, false);
        st.show(slide);
        gridStages.push(st);
    });
    $('.cp-grid-item.is-active', grid)?.scrollIntoView({ block: 'center' });
}

let gridCloseTimer;
$('#grid').addEventListener('click', (e) => {
    const b = e.target.closest('.cp-grid-item');
    if (!b) return;
    const i = Number(b.dataset.index);
    pick(i);
    clearTimeout(gridCloseTimer);
    // Beri waktu untuk klik dua kali (tayangkan) sebelum menutup
    gridCloseTimer = setTimeout(() => toggleGrid(false), S.presenting ? 320 : 0);
});
$('#grid').addEventListener('dblclick', (e) => {
    const b = e.target.closest('.cp-grid-item');
    if (!b) return;
    clearTimeout(gridCloseTimer);
    take(Number(b.dataset.index));
    toggleGrid(false);
});

// ---------- Menu klik kanan ----------
const menu = $('#ctx-menu');
function closeMenu() { menu.hidden = true; menu.replaceChildren(); }
function openMenu(x, y, items) {
    menu.replaceChildren();
    items.forEach((it) => {
        if (it === '-') { menu.appendChild(Object.assign(document.createElement('hr'), {})); return; }
        const b = document.createElement('button');
        b.type = 'button';
        b.setAttribute('role', 'menuitem');
        b.textContent = it.label;
        if (it.checked) b.classList.add('is-checked');
        if (it.danger) b.classList.add('is-danger');
        if (it.key) { const k = document.createElement('kbd'); k.textContent = it.key; b.appendChild(k); }
        b.addEventListener('click', () => { closeMenu(); it.run(); });
        menu.appendChild(b);
    });
    menu.hidden = false;
    const r = menu.getBoundingClientRect();
    menu.style.left = `${Math.min(x, innerWidth - r.width - 8)}px`;
    menu.style.top = `${Math.min(y, innerHeight - r.height - 8)}px`;
}
document.addEventListener('click', (e) => { if (!menu.hidden && !menu.contains(e.target)) closeMenu(); });
window.addEventListener('blur', closeMenu);
window.addEventListener('resize', closeMenu);

function slideMenu(i) {
    const hidden = isHidden(i);
    return S.presenting
        ? [
            { label: `Tayangkan slide ${i + 1} sekarang`, run: () => setLive(i) },
            { label: 'Siapkan sebagai berikutnya', run: () => cue(i) },
            '-',
            { label: hidden ? 'Tampilkan slide' : 'Sembunyikan slide', key: 'H', checked: hidden, run: () => toggleHidden(i) },
            { label: 'Edit isi slide', key: 'E', run: () => editSlide(i) },
        ]
        : [
            { label: `Mulai tayang dari slide ${i + 1}`, key: '⇧F5', run: () => startShow(i) },
            '-',
            { label: hidden ? 'Tampilkan slide' : 'Sembunyikan slide', key: 'H', checked: hidden, run: () => toggleHidden(i) },
            { label: 'Edit isi slide', key: 'E', run: () => { select(i); editSlide(i); } },
        ];
}

document.addEventListener('contextmenu', (e) => {
    const item = e.target.closest('.cp-thumb, .cp-grid-item');
    const onStage = e.target.closest('#stage-current');
    if (!item && !onStage) return;
    e.preventDefault();
    if (item) { openMenu(e.clientX, e.clientY, slideMenu(Number(item.dataset.index))); return; }
    openMenu(e.clientX, e.clientY, S.presenting
        ? [
            { label: 'Berikutnya', key: '→', run: advance },
            { label: 'Sebelumnya', key: '←', run: nav.prev },
            { label: 'Semua slide…', key: 'G', run: () => toggleGrid(true) },
            '-',
            { label: 'Layar hitam', key: 'B', checked: S.blank === 'black', run: () => toggleBlank('black') },
            { label: 'Layar putih', key: 'W', checked: S.blank === 'white', run: () => toggleBlank('white') },
            { label: 'Sembunyikan teks', key: 'T', checked: S.hideText, run: toggleHideText },
            '-',
            { label: 'Edit isi slide ini', key: 'E', run: () => editSlide(S.live) },
            { label: 'Akhiri tayangan', key: 'Esc', danger: true, run: endShow },
        ]
        : slideMenu(S.sel));
});

// =============================================================================
// Pengaturan tampilan (Desain & Tampilan)
// =============================================================================
const saveSettings = debounce(async () => {
    setSaveStatus('saving');
    try {
        const r = await fetch(cfg.routes.settings, { method: 'POST', headers: jsonHeaders(csrf), body: JSON.stringify({ settings: S.settings }) });
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        setSaveStatus('saved');
    } catch {
        setSaveStatus('error');
    }
}, 700);

function applySettings({ save = true } = {}) {
    current.setSettings(S.settings);
    next.setSettings(S.settings);
    thumbStages.forEach((st, i) => { st.setSettings(S.settings, false); st.show(S.slides[i]); });
    refreshBgTiles();
    syncInputs();
    updateFontUi();
    applyBlankUi();
    markActive();
    channel.send({ type: 'settings', settings: S.settings });
    if (save) { setSaveStatus('pending'); saveSettings(); }
}

const fmt = {
    'design.speed': (v) => `${v}×`,
    'design.intensity': (v) => `${Math.round(v * 100)}%`,
    'design.shadowStrength': (v) => `${Math.round(v * 100)}%`,
};

function syncInputs() {
    $$('[data-setting]').forEach((inp) => {
        const v = getPath(S.settings, inp.dataset.setting);
        if (inp.type === 'checkbox') inp.checked = !!v;
        else if (document.activeElement !== inp) inp.value = v;
    });
    $$('[data-out]').forEach((o) => { const v = getPath(S.settings, o.dataset.out); o.textContent = fmt[o.dataset.out] ? fmt[o.dataset.out](v) : v; });
    $$('.cp-bg-tile').forEach((t) => t.classList.toggle('is-active', t.dataset.bg === S.settings.design.bg));
    setPressed('guides', !!S.settings.guides);

    const sc = S.settings.screen;
    const preset = SCREEN_PRESETS.find((d) => {
        const s = screenFromDiagonal(d);
        return Math.abs(s.widthCm - sc.widthCm) < 0.3 && Math.abs(s.heightCm - sc.heightCm) < 0.3;
    });
    $('#screen-preset').value = preset ? String(preset) : 'custom';

    const L = computeLayout(S.settings);
    const boxW = 1920 - 2 * L.side;
    const boxH = 1080 - L.top - L.bottom;
    const cm = (px, axis) => (px * (axis === 'x' ? sc.widthCm / 1920 : sc.heightCm / 1080)).toFixed(1).replace('.', ',');
    $('#layout-info').innerHTML = `Area atas: <b>${L.top}px</b><br>Kotak tulisan: <b>${boxW}×${boxH}px</b><br>≈ ${cm(boxW, 'x')} × ${cm(boxH, 'y')} cm`;
    $('#layout-info').classList.toggle('is-warn', boxH < 160);
    updateContrast();
}

$$('[data-setting]').forEach((inp) => {
    const evt = (inp.type === 'number' || inp.tagName === 'SELECT' || inp.type === 'checkbox') ? 'change' : 'input';
    inp.addEventListener(evt, () => {
        let v;
        if (inp.type === 'checkbox') v = inp.checked;
        else if (inp.type === 'number' || inp.type === 'range') {
            v = parseFloat(inp.value);
            if (!Number.isFinite(v)) { syncInputs(); return; }
            v = Math.max(Number(inp.min || 0), v);
        } else v = inp.value;
        setPath(S.settings, inp.dataset.setting, v);
        // Kembalikan fokus ke navigasi slide: tanpa ini, Spasi/panah berikutnya mengubah
        // centang/warna/slider ini lagi alih-alih memindah slide.
        if (['checkbox', 'color', 'range'].includes(inp.type) || inp.tagName === 'SELECT') inp.blur();
        const sc = S.settings.screen;
        if (sc.marginTopCm + sc.marginBottomCm > sc.heightCm * 0.85 || sc.marginSideCm * 2 > sc.widthCm * 0.8) {
            toast('Batas terlalu besar untuk ukuran layar ini. Kotak tulisan hampir tidak tersisa.');
        }
        applySettings();
    });
});

$('#screen-preset').addEventListener('change', (e) => {
    if (e.target.value === 'custom') {
        $('[data-setting="screen.widthCm"]').focus();
        return;
    }
    Object.assign(S.settings.screen, screenFromDiagonal(Number(e.target.value)));
    applySettings();
});

// Galeri latar: pratinjau diam, beranimasi saat kursor di atasnya
const bgTileStages = new Map();
function refreshBgTiles() {
    $$('.cp-bg-tile').forEach((tile) => {
        let st = bgTileStages.get(tile);
        if (!st) {
            st = new Stage(tile.querySelector('.cp-bg-preview'), { animated: false, guides: false });
            bgTileStages.set(tile, st);
            tile.addEventListener('mouseenter', () => applyBackground(st.bg, st.settings.design, true));
            tile.addEventListener('mouseleave', () => applyBackground(st.bg, st.settings.design, false));
            tile.addEventListener('click', () => { S.settings.design.bg = tile.dataset.bg; applySettings(); });
        }
        st.setSettings({ ...S.settings, guides: false, design: { ...S.settings.design, bg: tile.dataset.bg, powerSave: false } }, false);
    });
    refreshSongStyleTiles();
}

// Galeri gaya slide lirik: pratinjau memakai kanvas asli dengan contoh bait
const SAMPLE_LYRIC = { key: 'contoh-gaya-lirik', type: 'song_lyric', title: 'Contoh Lagu', watermark: '1', is_reff: false, content: 'Haleluya, pujilah Tuhan\nhai segenap bumi' };
const songStyleStages = new Map();
function refreshSongStyleTiles() {
    $$('.cp-ss-tile').forEach((tile) => {
        let st = songStyleStages.get(tile);
        if (!st) {
            st = new Stage(tile.querySelector('.cp-ss-preview'), { animated: false, guides: false });
            songStyleStages.set(tile, st);
            tile.addEventListener('click', () => { S.settings.design.songStyle = tile.dataset.songStyle; applySettings(); tile.blur(); });
        }
        st.setSettings({ ...S.settings, guides: false, fonts: {}, design: { ...S.settings.design, songStyle: tile.dataset.songStyle, powerSave: true } }, false);
        st.show(SAMPLE_LYRIC);
        tile.classList.toggle('is-active', (S.settings.design.songStyle || 'nomor') === tile.dataset.songStyle);
    });
}

// ---------- Ukuran huruf per slide (slide yang tampil besar) ----------
function updateFontUi() {
    const px = current.fontSize;
    $$('[data-font-value]').forEach((el) => {
        if (!px) { el.textContent = '-'; return; }
        const cm = (px * S.settings.screen.heightCm / 1080).toFixed(1).replace('.', ',');
        el.textContent = `${cm} cm${current.fontManual ? '' : ' · otomatis'}`;
        el.title = `${px}px pada kanvas 1920×1080`;
        el.classList.toggle('is-manual', current.fontManual);
    });
}

function changeFont(delta) {
    const slide = S.slides[mainIndex()];
    if (!slide || !current.fontSize) return;
    S.settings.fonts = { ...S.settings.fonts, [slide.key]: Math.max(20, Math.min(240, current.fontSize + delta)) };
    applySettings();
}

function resetFont() {
    const slide = S.slides[mainIndex()];
    if (!slide || !(slide.key in S.settings.fonts)) return;
    const fonts = { ...S.settings.fonts };
    delete fonts[slide.key];
    S.settings.fonts = fonts;
    applySettings();
}

// =============================================================================
// Editor isi (panel kanan)
// =============================================================================
let editorOpen = false;
function toggleEditor(force) {
    editorOpen = force ?? !editorOpen;
    $('#editor').classList.toggle('is-open', editorOpen);
    $('#editor').setAttribute('aria-hidden', editorOpen ? 'false' : 'true');
    setPressed('toggle-editor', editorOpen);
}

function editSlide(i = mainIndex()) {
    toggleEditor(true);
    const slide = S.slides[i];
    const m = slide?.key.match(/^i(\d+)/);
    const target = (m && $(`#ed-item-${m[1]}`)) || $('#liveForm .ed-card');
    if (target) {
        target.scrollIntoView({ block: 'start', behavior: 'smooth' });
        target.classList.add('is-flash');
        setTimeout(() => target.classList.remove('is-flash'), 1400);
    }
}

let saveState = 'saved';
function setSaveStatus(state) {
    saveState = state;
    const el = $('#status-save');
    el.textContent = {
        pending: 'Ada perubahan, menyimpan sebentar lagi…',
        saving: 'Menyimpan…',
        saved: 'Semua perubahan tersimpan',
        error: 'Gagal menyimpan. Periksa koneksi, lalu tekan Ctrl+S',
    }[state];
    el.className = `is-${state}`;
}

function setSlides(slides) {
    const keyOf = (i) => (i === null ? null : S.slides[i]?.key);
    const liveKey = keyOf(S.live);
    const selKeyNow = keyOf(S.sel);
    const cueKey = keyOf(S.cue);
    S.slides = slides;
    const find = (key, fallback) => { const i = slides.findIndex((s) => s.key === key); return i >= 0 ? i : fallback; };
    S.live = clamp(find(liveKey, S.live));
    S.sel = clamp(find(selKeyNow, S.sel));
    S.cue = cueKey ? (find(cueKey, -1) >= 0 ? find(cueKey, -1) : null) : null;
    shownMain = null;
    buildThumbs();
    buildOutline();
    render();
    channel.send({ type: 'slides', slides: S.slides, settings: S.settings });
    sendState();
}

const editor = initEditor({
    form: $('#liveForm'),
    csrf,
    routes: cfg.routes,
    onSaved: (data) => { if (Array.isArray(data.slides)) setSlides(data.slides); },
    onStatus: setSaveStatus,
});

window.addEventListener('beforeunload', (e) => {
    if (saveState === 'pending' || saveState === 'saving') { e.preventDefault(); e.returnValue = ''; }
});

// =============================================================================
// Ribbon, tombol, timer, keyboard
// =============================================================================
function selectTab(name) {
    $$('.cp-tab[data-tab]').forEach((t) => { const on = t.dataset.tab === name; t.classList.toggle('is-active', on); t.setAttribute('aria-selected', on ? 'true' : 'false'); });
    $$('.cp-panel').forEach((p) => p.classList.toggle('is-active', p.dataset.panel === name));
    if (name === 'edit') toggleEditor(true);
    if (name !== 'edit') { try { localStorage.setItem('cp-tab', name); } catch { /* abaikan */ } }
}
$$('.cp-tab[data-tab]').forEach((t) => t.addEventListener('click', () => selectTab(t.dataset.tab)));
try {
    const lastTab = localStorage.getItem('cp-tab');
    if (lastTab && document.querySelector(`.cp-tab[data-tab="${CSS.escape(lastTab)}"]`)) selectTab(lastTab);
} catch { /* abaikan */ }

function fmtTime(ms) {
    const s = Math.floor(ms / 1000);
    return [Math.floor(s / 3600), Math.floor((s % 3600) / 60), s % 60].map((n) => String(n).padStart(2, '0')).join(':');
}
setInterval(() => {
    $('#timer').textContent = fmtTime((timerPausedAt ?? Date.now()) - timerStart);
    $('#clock').textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}, 500);

const commands = {
    first: nav.first,
    prev: nav.prev,
    next: nav.next,
    last: nav.last,
    'start-begin': () => startShow(firstVisible()),
    'start-here': () => startShow(mainIndex()),
    'end-show': endShow,
    'cue-clear': () => { S.cue = null; render(); },
    search: () => openSearch(),
    check: () => runCheck(),
    'phone-remote': () => openPhoneRemote(),
    'song-insert': () => openSongDialog(),
    ticker: () => openTicker(),
    'toggle-hidden': () => toggleHidden(),
    black: () => toggleBlank('black'),
    white: () => toggleBlank('white'),
    'hide-text': toggleHideText,
    grid: () => toggleGrid(),
    'edit-current': () => editSlide(),
    'font-up': () => changeFont(6),
    'font-down': () => changeFont(-6),
    'font-reset': resetFont,
    'open-projector': openProjector,
    'copy-remote': copyRemoteLink,
    guides: () => { S.settings.guides = !S.settings.guides; applySettings(); toast(S.settings.guides ? 'Garis batas tampil di layar live.' : 'Garis batas disembunyikan.'); },
    'toggle-editor': () => toggleEditor(),
    'save-now': () => editor.save(),
    undo: () => toast(editor.undo() ? 'Perubahan terakhir diurungkan.' : 'Tidak ada perubahan yang bisa diurungkan.'),
    help: () => $('#help').showModal(),
    'timer-toggle': () => {
        if (timerPausedAt) { timerStart += Date.now() - timerPausedAt; timerPausedAt = null; } else timerPausedAt = Date.now();
        $('#timer-toggle').textContent = timerPausedAt ? 'Lanjut' : 'Jeda';
    },
    'timer-reset': () => { timerStart = Date.now(); if (timerPausedAt) timerPausedAt = timerStart; },
};

document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-cmd]');
    if (!el || !commands[el.dataset.cmd]) return;
    e.preventDefault();
    el.blur();
    commands[el.dataset.cmd]();
});

$('#slide-input').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); nav.to(parseInt(e.target.value, 10) - 1); e.target.blur(); }
});
$('#slide-input').addEventListener('change', (e) => { if (!S.presenting) select(parseInt(e.target.value, 10) - 1); });

let jumpBuffer = '';
let jumpTimer;
function showJump() {
    const el = $('#jump');
    el.hidden = false;
    el.textContent = `${S.presenting ? 'Tayangkan' : 'Pilih'} slide ${jumpBuffer}… (Enter)`;
    clearTimeout(jumpTimer);
    jumpTimer = setTimeout(clearJump, 3000);
}
function clearJump() {
    jumpBuffer = '';
    $('#jump').hidden = true;
}

let escArmedAt = 0;

document.addEventListener('keydown', (e) => {
    const t = e.target;
    // Kotak centang/warna/slider bukan tempat mengetik: pintasan tetap jalan, dan Spasi tidak
    // boleh mencentang/menghapus centang pengaturan secara tak sengaja.
    const toggleInput = t.tagName === 'INPUT' && ['checkbox', 'radio', 'color', 'range', 'button'].includes(t.type);
    const typing = (['INPUT', 'TEXTAREA', 'SELECT'].includes(t.tagName) && !toggleInput) || t.isContentEditable;
    if (toggleInput && [' ', 'Enter'].includes(e.key)) { e.preventDefault(); t.blur(); }
    const k = e.key;

    if ((e.ctrlKey || e.metaKey) && k.toLowerCase() === 's') { e.preventDefault(); editor.save(); return; }
    if (k === 'F5') { e.preventDefault(); if (e.shiftKey) startShow(mainIndex()); else startShow(firstVisible()); return; }
    if ((e.ctrlKey || e.metaKey) && k.toLowerCase() === 'f') { e.preventDefault(); openSearch(); return; }
    if ($$('dialog').some((d) => d.open)) return;
    if (!menu.hidden && k === 'Escape') { closeMenu(); return; }
    if (typing) { if (k === 'Escape') t.blur(); return; }

    if (e.ctrlKey || e.metaKey) {
        if (k === '=' || k === '+') { e.preventDefault(); changeFont(6); }
        else if (k === '-') { e.preventDefault(); changeFont(-6); }
        else if (k === '0') { e.preventDefault(); resetFont(); }
        else if (k.toLowerCase() === 'z' && !e.shiftKey) { e.preventDefault(); commands.undo(); }
        return;
    }
    if (e.altKey) return;

    if (/^[0-9]$/.test(k)) { jumpBuffer += k; showJump(); return; }
    if (k === 'Enter' && jumpBuffer) { e.preventDefault(); nav.to(parseInt(jumpBuffer, 10) - 1); clearJump(); return; }

    if (k === 'Escape') {
        if (jumpBuffer) clearJump();
        else if (!$('#grid-overlay').hidden) toggleGrid(false);
        else if (editorOpen) toggleEditor(false);
        else if (S.presenting && S.cue !== null) { S.cue = null; render(); }
        else if (S.blank || S.hideText) { S.blank = null; S.hideText = false; applyBlankUi(); sendState(); }
        else if (S.presenting) {
            // Seperti PowerPoint Esc = akhiri, tetapi minta tekan 2× agar tidak tak sengaja
            if (Date.now() - escArmedAt < 2500) { escArmedAt = 0; endShow(); }
            else { escArmedAt = Date.now(); toast('Tekan Esc sekali lagi untuk mengakhiri tayangan.'); }
        }
        return;
    }

    if (S.presenting) {
        if (['ArrowRight', 'ArrowDown', 'PageDown', ' ', 'Enter', 'n', 'N'].includes(k)) { e.preventDefault(); advance(); return; }
        if (['ArrowLeft', 'ArrowUp', 'PageUp', 'Backspace', 'p', 'P'].includes(k)) { e.preventDefault(); nav.prev(); return; }
    } else {
        // Mode persiapan: panah hanya memindahkan pilihan (seperti panel thumbnail PowerPoint)
        if (['ArrowRight', 'ArrowDown', 'PageDown'].includes(k)) { e.preventDefault(); select(S.sel + 1); return; }
        if (['ArrowLeft', 'ArrowUp', 'PageUp'].includes(k)) { e.preventDefault(); select(S.sel - 1); return; }
    }
    if (k === 'Home') { e.preventDefault(); nav.first(); }
    else if (k === 'End') { e.preventDefault(); nav.last(); }
    else if (k === 'b' || k === 'B' || k === '.') toggleBlank('black');
    else if (k === 'w' || k === 'W' || k === ',') toggleBlank('white');
    else if (k === 't' || k === 'T') toggleHideText();
    else if (k === 'g' || k === 'G') toggleGrid();
    else if (k === 'e' || k === 'E') editSlide();
    else if (k === 'h' || k === 'H') toggleHidden();
    else if (k === '/') { e.preventDefault(); openSearch(); }
    // preventDefault: huruf pintasan tidak boleh ikut terketik di kolom dialog yang langsung difokus
    else if (k === 'l' || k === 'L') { e.preventDefault(); openSongDialog(); }
    else if (k === 'k' || k === 'K') { e.preventDefault(); openTicker(); }
    else if (k === '?') $('#help').showModal();
});

// =============================================================================
// Cari slide (Ctrl+F)
// =============================================================================
const norm = (t) => String(t ?? '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9\s]/g, ' ').replace(/\s+/g, ' ').trim();
let searchHits = [];
let searchSel = 0;

function openSearch() {
    closeMenu();
    const dlg = $('#search-dlg');
    if (!dlg.open) dlg.showModal();
    $('#search-input').select();
    runSearch();
}

function runSearch() {
    const q = norm($('#search-input').value);
    const list = $('#search-results');
    list.replaceChildren();
    $('#search-hint').textContent = S.presenting
        ? '↑↓ pilih · Enter = siapkan sebagai berikutnya · Shift+Enter / klik dua kali = tayangkan sekarang · Esc tutup'
        : '↑↓ pilih · Enter = buka slide · Esc tutup';
    searchHits = [];
    if (!q) return;
    const words = q.split(' ');
    const num = /^\d+$/.test(q) ? Number(q) - 1 : -1;
    S.slides.forEach((s, i) => {
        const hay = norm([s.section, s.title, s.content, s.speaker, s.ref, (s.verses || []).map((v) => v.text).join(' ')].join(' '));
        if (i === num || words.every((w) => hay.includes(w))) searchHits.push(i);
    });
    // Lompatan paling berguna di atas: sampul lagu, lalu urut nomor slide
    searchHits.sort((a, b) => (S.slides[b].type === 'song_cover') - (S.slides[a].type === 'song_cover') || a - b);
    searchHits = searchHits.slice(0, 40);
    searchSel = 0;
    if (!searchHits.length) {
        const li = document.createElement('li');
        li.className = 'is-empty';
        li.textContent = 'Tidak ditemukan. Untuk lagu yang belum ada di jadwal, pakai Lagu Dadakan (L).';
        list.appendChild(li);
        return;
    }
    searchHits.forEach((i, n) => {
        const s = S.slides[i];
        const li = document.createElement('li');
        const b = document.createElement('button');
        b.type = 'button';
        b.dataset.index = i;
        if (n === 0) b.className = 'is-sel';
        const text = String(s.type === 'song_cover' ? s.content : (s.content || s.title || '')).replace(/\s+/g, ' ').slice(0, 110);
        b.innerHTML = `<b>${i + 1}</b><span class="cp-sr-sec"></span><span class="cp-sr-text"></span>${isHidden(i) ? '<em>tersembunyi</em>' : ''}${i === S.live && S.presenting ? '<em class="is-live">LIVE</em>' : ''}`;
        b.querySelector('.cp-sr-sec').textContent = s.section;
        b.querySelector('.cp-sr-text').textContent = text;
        li.appendChild(b);
        list.appendChild(li);
    });
}

function searchMark() {
    $$('#search-results button').forEach((b, n) => b.classList.toggle('is-sel', n === searchSel));
    $$('#search-results button')[searchSel]?.scrollIntoView({ block: 'nearest' });
}

function searchChoose(i, now = false) {
    $('#search-dlg').close();
    if (now && S.presenting) setLive(i); else pick(i);
    if (!S.presenting) $(`.cp-thumb[data-index="${i}"]`)?.scrollIntoView({ block: 'center' });
}

$('#search-input').addEventListener('input', runSearch);
$('#search-input').addEventListener('keydown', (e) => {
    if (e.key === 'ArrowDown') { e.preventDefault(); searchSel = Math.min(searchHits.length - 1, searchSel + 1); searchMark(); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); searchSel = Math.max(0, searchSel - 1); searchMark(); }
    else if (e.key === 'Enter' && searchHits.length) { e.preventDefault(); searchChoose(searchHits[searchSel], e.shiftKey); }
});
$('#search-results').addEventListener('click', (e) => { const b = e.target.closest('button[data-index]'); if (b) searchChoose(Number(b.dataset.index)); });
$('#search-results').addEventListener('dblclick', (e) => { const b = e.target.closest('button[data-index]'); if (b) searchChoose(Number(b.dataset.index), true); });

// =============================================================================
// Lagu dadakan: sisipkan lagu dari database setelah bagian yang sedang tampil besar
// =============================================================================
let sdFound = null;

/** Bagian liturgi tempat lagu disisipkan: bagian slide ini, atau terdekat (sampul/warta bukan bagian). */
function partIndex() {
    const isPart = (i) => /^i\d+/.test(S.slides[i]?.key || '');
    for (let i = mainIndex(); i >= 0; i--) if (isPart(i)) return i;
    for (let i = mainIndex(); i < S.slides.length; i++) if (isPart(i)) return i;
    return mainIndex();
}

function openSongDialog() {
    closeMenu();
    const dlg = $('#song-dlg');
    sdFound = null;
    $('#sd-preview').replaceChildren();
    $('[data-sd="insert"]').disabled = true;
    const here = S.slides[partIndex()];
    $('#sd-where').textContent = `Disisipkan setelah bagian "${here?.section ?? '-'}"${S.presenting ? ' dan disiapkan sebagai slide berikutnya.' : '.'}`;
    if (!dlg.open) dlg.showModal();
    $('#sd-num').select();
}

$('#song-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const book = $('#sd-book').value;
    const number = $('#sd-num').value.trim();
    const box = $('#sd-preview');
    if (!number) return;
    box.textContent = 'Mencari…';
    sdFound = null;
    $('[data-sd="insert"]').disabled = true;
    try {
        const r = await fetch(`${cfg.routes.lagu}?buku=${encodeURIComponent(book)}&nomor=${encodeURIComponent(number)}`, { headers: jsonHeaders(csrf) });
        const data = await r.json();
        if (!data.success) { box.textContent = data.message || 'Lagu tidak ditemukan.'; return; }
        sdFound = { book, number };
        const pieces = String(data.text).split('===SLIDE_BREAK===');
        box.innerHTML = '<strong class="cp-sd-title"></strong><p class="cp-muted">Bait yang dinyanyikan:</p><div class="cp-sd-verses"></div>';
        box.querySelector('.cp-sd-title').textContent = data.judul;
        const wrap = box.querySelector('.cp-sd-verses');
        (data.labels || []).forEach((label, n) => {
            if (label === 'Reff') return;
            const l = document.createElement('label');
            l.className = 'cp-check';
            l.innerHTML = '<input type="checkbox" checked><span></span>';
            l.querySelector('input').value = label;
            l.querySelector('span').textContent = `${label}. ${(pieces[n] || '').trim().split('\n')[0]}`;
            wrap.appendChild(l);
        });
        $('[data-sd="insert"]').disabled = false;
        $('[data-sd="insert"]').focus();
    } catch {
        box.textContent = 'Gagal menghubungi server. Periksa koneksi.';
    }
});

$('#song-dlg').addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-sd]');
    if (!btn) return;
    if (btn.dataset.sd === 'close') { $('#song-dlg').close(); return; }
    if (btn.dataset.sd !== 'insert' || !sdFound) return;
    const verses = $$('#sd-preview input:checked').map((c) => Number(c.value)).filter(Boolean);
    btn.disabled = true;
    try {
        const r = await fetch(cfg.routes.insertSong, {
            method: 'POST',
            headers: jsonHeaders(csrf),
            body: JSON.stringify({ buku: sdFound.book, nomor: sdFound.number, bait: verses, after_key: S.slides[partIndex()]?.key }),
        });
        const data = await r.json();
        if (!r.ok || !data.success) throw new Error(data.message || `HTTP ${r.status}`);
        setSlides(data.slides);
        const at = S.slides.findIndex((s) => s.key === data.coverKey);
        $('#song-dlg').close();
        if (at >= 0) pick(at);
        toast(`${data.judul} disisipkan${S.presenting ? '. Tekan → untuk menayangkan.' : '.'}`);
    } catch (err) {
        toast(`Gagal menyisipkan lagu: ${err.message}`);
        btn.disabled = false;
    }
});

// =============================================================================
// Teks cepat di bawah layar (tanpa mengganti slide)
// =============================================================================
const tickerKey = 'gpi:ticker:recent';
const recentTickers = () => { try { return JSON.parse(read(tickerKey) || '[]'); } catch { return []; } };

function openTicker() {
    closeMenu();
    const inp = $('#ticker-input');
    if (S.ticker) inp.value = S.ticker;
    const box = $('#ticker-recent');
    box.replaceChildren();
    recentTickers().forEach((t) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'cp-chip';
        b.textContent = t;
        b.addEventListener('click', () => { inp.value = t; inp.focus(); });
        box.appendChild(b);
    });
    const dlg = $('#ticker-dlg');
    if (!dlg.open) dlg.showModal();
    inp.focus();
}

function setTicker(text) {
    const t = String(text ?? '').trim().slice(0, 200);
    S.ticker = t || null;
    if (t) write(tickerKey, JSON.stringify([t, ...recentTickers().filter((x) => x !== t)].slice(0, 6)));
    applyBlankUi();
    sendState();
    toast(t ? 'Teks cepat tampil di layar live.' : 'Teks cepat disembunyikan.');
}

$('#ticker-dlg').addEventListener('click', (e) => {
    const act = e.target.closest('[data-tk]')?.dataset.tk;
    if (act === 'close') $('#ticker-dlg').close();
    if (act === 'show') { setTicker($('#ticker-input').value); $('#ticker-dlg').close(); }
    if (act === 'hide') { setTicker(null); $('#ticker-dlg').close(); }
});
$('#ticker-input').addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); setTicker(e.target.value); $('#ticker-dlg').close(); }
});

// =============================================================================
// Uji tayang: periksa semua slide di kanvas tersembunyi 1920x1080
// =============================================================================
const CHECK_MIN = { song_lyric: 56, text: 50, custom: 50, bible: 44, instruksi: 60 };

function runCheck() {
    const host = document.createElement('div');
    host.style.cssText = 'position:fixed;left:-40000px;top:0;width:1920px;height:1080px;visibility:hidden;';
    document.body.appendChild(host);
    const st = new Stage(host, { ...stageOpts, animated: false, guides: false });
    st.setSettings({ ...S.settings, guides: false }, false);
    const cm = (px) => (px * S.settings.screen.heightCm / 1080).toFixed(1).replace('.', ',');
    const problems = [];
    S.slides.forEach((s, i) => {
        if (s.type === 'announcements_slideshow') return;
        st.show(s);
        const px = st.fontSize;
        const min = CHECK_MIN[s.type];
        const empty = ['song_lyric', 'text', 'custom'].includes(s.type) && !String(s.content || '').trim();
        if (empty) problems.push({ i, msg: 'slide kosong' });
        else if (min && px && px < min) problems.push({ i, msg: `huruf kecil (${cm(px)} cm)` });
        const box = host.querySelector('.pv-box');
        const fit = box?.firstElementChild;
        if (fit && (fit.offsetHeight > box.clientHeight + 1 || fit.scrollWidth > box.clientWidth + 1)) problems.push({ i, msg: 'teks terpotong' });
    });
    st.destroy();
    host.remove();

    const hidden = (S.settings.hidden || []).length;
    $('#check-summary').textContent = `${S.slides.length} slide diperiksa${hidden ? `, ${hidden} disembunyikan` : ''}. `
        + (problems.length ? `${problems.length} perlu diperiksa. Klik untuk membuka slidenya.` : 'Semua aman: tidak ada teks terpotong, huruf terlalu kecil, atau slide kosong.');
    const list = $('#check-results');
    list.replaceChildren();
    problems.forEach(({ i, msg }) => {
        const li = document.createElement('li');
        const b = document.createElement('button');
        b.type = 'button';
        b.dataset.index = i;
        b.innerHTML = '<b></b><span class="cp-sr-sec"></span><span class="cp-sr-text"></span>';
        b.querySelector('b').textContent = i + 1;
        b.querySelector('.cp-sr-sec').textContent = S.slides[i].section;
        b.querySelector('.cp-sr-text').textContent = msg;
        li.appendChild(b);
        list.appendChild(li);
    });
    const dlg = $('#check-dlg');
    if (!dlg.open) dlg.showModal();
}

$('#check-results').addEventListener('click', (e) => {
    const b = e.target.closest('button[data-index]');
    if (!b) return;
    $('#check-dlg').close();
    pick(Number(b.dataset.index));
});
$('#check-dlg').addEventListener('click', (e) => { if (e.target.closest('[data-ck="close"]')) $('#check-dlg').close(); });
// =============================================================================
// Warna masa raya gerejawi & peringatan kontras
// =============================================================================
const PRESETS = {
    standar: ['#1b2735', '#2b6cb0', '#05070d'],
    biasa: ['#0f2e1d', '#2f855a', '#04100a'],
    adven: ['#2a1a4a', '#6b46c1', '#0b0614'],
    natal: ['#1f2a44', '#c9a227', '#070a12'],
    sengsara: ['#2d0f2f', '#7b2d6b', '#0c040d'],
    paskah: ['#10233a', '#d4af37', '#04080f'],
    pentakosta: ['#3a0d0d', '#c53030', '#0d0303'],
};
$('#season-preset').addEventListener('change', (e) => {
    const sel = e.target;
    const p = PRESETS[sel.value];
    if (!p) return;
    [S.settings.design.color1, S.settings.design.color2, S.settings.design.color3] = p;
    applySettings();
    toast(`Warna ${sel.options[sel.selectedIndex].text} dipakai.`);
    sel.value = ''; // menu kembali ke "Warna masa raya…", warnanya tetap terlihat di kotak warna
    sel.blur();
});

const luminance = (hex) => {
    const c = String(hex || '#000000').replace('#', '').match(/../g).map((h) => parseInt(h, 16) / 255).map((v) => (v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4));
    return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
};
const contrast = (a, b) => { const [x, y] = [luminance(a), luminance(b)].sort((m, n) => n - m); return (x + 0.05) / (y + 0.05); };

/** Bandingkan warna teks & lirik dengan warna latar paling terang (Utama/Aksen). */
function updateContrast() {
    const d = S.settings.design;
    const bg = [d.color1, d.color2].sort((a, b) => luminance(b) - luminance(a))[0];
    const issues = [];
    const t = contrast(d.textColor, bg);
    if (t < 4.5) issues.push(`teks ${t.toFixed(1).replace('.', ',')}:1`);
    if (d.songMark !== false) {
        const s = contrast(d.songColor, bg);
        if (s < 4.5) issues.push(`lirik ${s.toFixed(1).replace('.', ',')}:1`);
    }
    const el = $('#contrast-info');
    el.hidden = !issues.length;
    el.textContent = issues.length ? `Kontras rendah (${issues.join(', ')}), sulit dibaca dari jauh.` : '';
}
// =============================================================================
// Remote dari HP: tampilkan QR & ikuti perintah HP (lewat server)
// =============================================================================
function openPhoneRemote() {
    const url = new URL(cfg.routes.remote, location.href).href;
    $('#remote-url').value = url;
    const qr = qrcode(0, 'M');
    qr.addData(url);
    qr.make();
    $('#remote-qr').innerHTML = qr.createSvgTag({ cellSize: 5, margin: 2, scalable: true });
    const dlg = $('#remote-dlg');
    if (!dlg.open) dlg.showModal();
}
$('#remote-dlg').addEventListener('click', async (e) => {
    const act = e.target.closest('[data-rq]')?.dataset.rq;
    if (act === 'close') $('#remote-dlg').close();
    if (act === 'copy') { try { await navigator.clipboard.writeText($('#remote-url').value); toast('Link remote disalin.'); } catch { $('#remote-url').select(); } }
});

let remoteFirst = true;
pollServerState(cfg.routes.stateGet, (st) => {
    // Lewati status lama saat halaman baru dibuka; hanya ikuti perintah baru dari HP
    if (remoteFirst) { remoteFirst = false; return; }
    if (!String(st.client || '').startsWith('remote')) return;
    S.blank = st.blank ?? null;
    S.hideText = !!st.hideText;
    let i = Number(st.index);
    if (st.key) { const k = S.slides.findIndex((s) => s.key === st.key); if (k >= 0) i = k; }
    if (!S.presenting) setMode(true);
    S.live = clamp(i);
    S.cue = null;
    write(indexKey, S.live);
    render();
    channel.send({ type: 'state', from: 'control', ...statePayload() });
}, () => {}, 1000);
// =============================================================================
// Mulai
// =============================================================================
document.body.classList.toggle('is-presenting', S.presenting);
current.setSettings(S.settings, false);
next.setSettings(S.settings, false);
syncInputs();
refreshBgTiles();
buildThumbs();
buildOutline();
render();
updateProjectorStatus();
sendState();
if (new URLSearchParams(location.search).has('uji')) {
    (document.fonts ? document.fonts.ready : Promise.resolve()).then(() => setTimeout(runCheck, 150));
}

setupOffline(cfg, (online) => {
    $('#status-net').hidden = online;
    if (!online) toast('Internet terputus. Tayangan di komputer ini tetap jalan.');
});
