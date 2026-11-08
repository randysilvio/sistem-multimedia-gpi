import { Stage, isCameraSlide } from './stage.js';
import { createChannel, pollServerState } from './sync.js';
import { safeStorage, stepVisible } from './util.js';
import { setupOffline } from './offline.js';

const cfg = window.PRESENTER;
const store = safeStorage();
const indexKey = `gpi:${cfg.scheduleId}:index`;

let slides = cfg.slides;
let settings = cfg.settings;
let version = cfg.version;
let blank = null;
let hideText = false;
let ticker = null;
const clamp = (i) => Math.max(0, Math.min(slides.length - 1, Number.isFinite(i) ? i : 0));
let index = clamp(Number(store?.getItem(indexKey)));

const stage = new Stage(document.getElementById('projector'), { animated: true, camera: 'live', logoUrl: cfg.logoUrl });

// ---------- Kamera (hanya dinyalakan bila ada slide berlatar kamera) ----------
const video = document.createElement('video');
video.autoplay = true;
video.muted = true;
video.playsInline = true;
stage.attachVideo(video);
let cameraState = 'off';
async function ensureCamera() {
    if (cameraState !== 'off') return;
    cameraState = 'starting';
    try {
        video.srcObject = await navigator.mediaDevices.getUserMedia({ video: { width: { ideal: 1920 }, height: { ideal: 1080 } }, audio: false });
        cameraState = 'on';
    } catch (err) {
        cameraState = 'off';
        console.warn('Kamera tidak bisa diakses (izin ditolak / bukan localhost/HTTPS):', err);
    }
}

function render(transition) {
    const slide = slides[index] ?? null;
    stage.show(slide, { transition });
    if (isCameraSlide(slide)) ensureCamera();
    try { store?.setItem(indexKey, String(index)); } catch { /* abaikan */ }
}

function applyState(st) {
    let target = Number(st.index);
    if (st.key) {
        const byKey = slides.findIndex((s) => s.key === st.key);
        if (byKey >= 0) target = byKey;
    }
    target = clamp(target);
    blank = st.blank ?? null;
    hideText = !!st.hideText;
    if (st.ticker !== undefined) { ticker = st.ticker || null; stage.setTicker(ticker); }
    stage.setBlank(blank);
    stage.setTextHidden(hideText);
    if (target !== index) {
        index = target;
        render(true);
    }
}

async function reloadFromServer() {
    try {
        const r = await fetch(cfg.routes.slides, { headers: { Accept: 'application/json' }, cache: 'no-store' });
        if (!r.ok) return;
        const data = await r.json();
        slides = data.slides;
        settings = data.settings;
        version = data.version;
        index = clamp(index);
        stage.setSettings(settings);
        render(false);
    } catch { /* coba lagi pada perubahan berikutnya */ }
}

stage.setSettings(settings);
render(false);

// ---------- Sinkronisasi ----------
let channel = null;
const syncDot = document.getElementById('sync-dot');

if (cfg.remote) {
    pollServerState(cfg.routes.state, async (st) => {
        if (st.contentVersion && st.contentVersion !== version) {
            version = st.contentVersion;
            await reloadFromServer();
        }
        applyState(st);
    }, (ok) => { syncDot.className = ok ? 'is-ok' : 'is-error'; });
} else {
    channel = createChannel(cfg.scheduleId, (msg) => {
        if (!msg || typeof msg !== 'object') return;
        if (msg.type === 'ping') channel.send({ type: 'pong' });
        if (msg.type === 'state') applyState(msg);
        if (msg.type === 'settings') { settings = msg.settings; stage.setSettings(settings); }
        if (msg.type === 'slides') {
            slides = msg.slides;
            if (msg.settings) { settings = msg.settings; stage.setSettings(settings); }
            index = clamp(index);
            render(false);
        }
    });
    channel.send({ type: 'hello' });

    // Remote HP: perintah dari HP lewat server. Layar live selalu terlihat di TV,
    // jadi ikut mendengarkan langsung (Kontrol Live bisa saja sedang diperkecil).
    let skipFirst = true;
    pollServerState(cfg.routes.state, async (st) => {
        if (skipFirst) { skipFirst = false; return; }
        if (!String(st.client || '').startsWith('remote')) return;
        if (st.contentVersion && st.contentVersion !== version) {
            version = st.contentVersion;
            await reloadFromServer();
        }
        applyState(st);
        broadcast(); // beri tahu Kontrol Live
    }, () => {}, 1000);
}

function broadcast() {
    channel?.send({ type: 'state', from: 'projector', index, key: slides[index]?.key ?? null, blank, hideText, ticker });
}

// ---------- Layar penuh & keyboard ----------
const overlay = document.getElementById('start-overlay');
function enterFullscreen() {
    overlay.hidden = true;
    const el = document.documentElement;
    if (!document.fullscreenElement) (el.requestFullscreen || el.webkitRequestFullscreen)?.call(el);
}
overlay.addEventListener('click', enterFullscreen);

document.addEventListener('keydown', (e) => {
    const k = e.key;
    if (k === 'f' || k === 'F') {
        if (document.fullscreenElement) document.exitFullscreen(); else enterFullscreen();
        return;
    }
    if (cfg.remote) return; // mode 2 komputer: layar ini hanya menampilkan
    if (['ArrowRight', 'ArrowDown', 'PageDown', ' ', 'Enter', 'n', 'N'].includes(k)) {
        e.preventDefault(); index = stepVisible(slides, settings.hidden, index, 1); render(true); broadcast();
    } else if (['ArrowLeft', 'ArrowUp', 'PageUp', 'Backspace', 'p', 'P'].includes(k)) {
        e.preventDefault(); index = stepVisible(slides, settings.hidden, index, -1); render(true); broadcast();
    } else if (k === 'Home' || k === 'End') {
        e.preventDefault(); index = k === 'Home' ? 0 : slides.length - 1; render(true); broadcast();
    } else if (k === 'b' || k === 'B' || k === '.') {
        blank = blank === 'black' ? null : 'black'; stage.setBlank(blank); broadcast();
    } else if (k === 'w' || k === 'W' || k === ',') {
        blank = blank === 'white' ? null : 'white'; stage.setBlank(blank); broadcast();
    }
});

// Sembunyikan kursor bila mouse diam
let cursorTimer;
document.addEventListener('mousemove', () => {
    document.body.classList.remove('hide-cursor');
    clearTimeout(cursorTimer);
    cursorTimer = setTimeout(() => document.body.classList.add('hide-cursor'), 2000);
});

setupOffline(cfg);
