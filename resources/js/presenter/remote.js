/*
 * Remote dari HP: tombol Berikutnya/Sebelumnya, layar hitam/putih, lompat ke bagian.
 * Bekerja lewat server (seperti mode 2 komputer): Kontrol Live & Layar Live
 * mengikuti perintah dari HP dalam ±1 detik.
 */
import { jsonHeaders, pollServerState, pushServerState } from './sync.js';
import { stepVisible } from './util.js';

const cfg = window.PRESENTER;
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const $ = (s) => document.querySelector(s);
const client = `remote-${Math.random().toString(36).slice(2, 8)}`;

let slides = cfg.slides;
let settings = cfg.settings;
let version = cfg.version;
const st = { index: 0, key: null, blank: null, hideText: false, ticker: null };

const summary = (s) => {
    if (!s) return 'Akhir';
    if (s.type === 'cover') return [s.title, s.date].filter(Boolean).join(' · ');
    if (s.type === 'song_cover') return s.content;
    if (s.type === 'bible') return (s.verses || []).map((v) => `${v.n ?? ''} ${v.text}`).join(' ');
    return [s.speaker && `[${s.speaker}]`, s.content || s.title].filter(Boolean).join(' ');
};

function render() {
    const i = st.index;
    const now = slides[i];
    const n = stepVisible(slides, settings.hidden, i, 1);
    $('#rm-now-num').textContent = `${i + 1} / ${slides.length}`;
    $('#rm-now-sec').textContent = now?.section ?? '';
    $('#rm-now-text').textContent = summary(now);
    $('#rm-next-num').textContent = n !== i ? `${n + 1}` : '';
    $('#rm-next-text').textContent = n !== i ? `${slides[n]?.section ?? ''}: ${summary(slides[n])}` : 'Akhir';
    document.querySelector('[data-rm="black"]').setAttribute('aria-pressed', st.blank === 'black');
    document.querySelector('[data-rm="white"]').setAttribute('aria-pressed', st.blank === 'white');
    document.querySelector('[data-rm="hide"]').setAttribute('aria-pressed', !!st.hideText);
    document.querySelectorAll('#rm-sections button').forEach((b) => b.classList.toggle('is-active', Number(b.dataset.start) <= i && i <= Number(b.dataset.end)));
}

function send() {
    st.key = slides[st.index]?.key ?? null;
    render();
    pushServerState(cfg.routes.push, csrf, { ...st, client }).then((ok) => { $('#rm-dot').className = `rm-dot ${ok ? 'is-ok' : 'is-error'}`; });
    if (navigator.vibrate) navigator.vibrate(15);
}

function buildSections() {
    const ol = $('#rm-sections');
    ol.replaceChildren();
    slides.forEach((s, i) => {
        if (i > 0 && s.section === slides[i - 1].section) return;
        let end = i;
        while (slides[end + 1] && slides[end + 1].section === s.section) end++;
        const li = document.createElement('li');
        const b = document.createElement('button');
        b.type = 'button';
        b.dataset.start = i;
        b.dataset.end = end;
        b.innerHTML = '<span></span><small></small>';
        b.querySelector('span').textContent = s.section;
        b.querySelector('small').textContent = end > i ? `${i + 1}–${end + 1}` : `${i + 1}`;
        li.appendChild(b);
        ol.appendChild(li);
    });
}

document.addEventListener('click', (e) => {
    const b = e.target.closest('[data-rm]');
    const sec = e.target.closest('#rm-sections button');
    if (sec) {
        // Satu ketukan tidak langsung menayangkan: minta konfirmasi singkat
        if (!confirm(`Tayangkan "${sec.querySelector('span').textContent}" (slide ${Number(sec.dataset.start) + 1})?`)) return;
        st.index = Number(sec.dataset.start);
        $('#rm-sections').hidden = true;
        send();
        return;
    }
    if (!b) return;
    switch (b.dataset.rm) {
        case 'next': { const n = stepVisible(slides, settings.hidden, st.index, 1); if (n !== st.index) { st.index = n; send(); } break; }
        case 'prev': { const p = stepVisible(slides, settings.hidden, st.index, -1); if (p !== st.index) { st.index = p; send(); } break; }
        case 'black': st.blank = st.blank === 'black' ? null : 'black'; send(); break;
        case 'white': st.blank = st.blank === 'white' ? null : 'white'; send(); break;
        case 'hide': st.hideText = !st.hideText; send(); break;
        case 'sections': $('#rm-sections').hidden = !$('#rm-sections').hidden; break;
        default:
    }
});

async function reload() {
    try {
        const r = await fetch(cfg.routes.slides, { headers: jsonHeaders(csrf), cache: 'no-store' });
        if (!r.ok) return;
        const data = await r.json();
        slides = data.slides;
        settings = data.settings;
        version = data.version;
        buildSections();
        render();
    } catch { /* coba lagi nanti */ }
}

pollServerState(cfg.routes.state, async (state) => {
    if (state.contentVersion && state.contentVersion !== version) await reload();
    let i = Number(state.index) || 0;
    if (state.key) { const k = slides.findIndex((s) => s.key === state.key); if (k >= 0) i = k; }
    Object.assign(st, { index: Math.max(0, Math.min(slides.length - 1, i)), blank: state.blank ?? null, hideText: !!state.hideText, ticker: state.ticker ?? null });
    render();
}, (ok) => { $('#rm-dot').className = `rm-dot ${ok ? 'is-ok' : 'is-error'}`; }, 1000);

buildSections();
render();
