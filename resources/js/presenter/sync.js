/**
 * Sinkronisasi control panel <-> layar live.
 *
 * 1 PC   : BroadcastChannel (instan, tanpa server). Fallback ke event `storage`
 *          untuk browser lama.
 * 2 PC   : control panel mengirim status ke server; layar live (?remote=1)
 *          mengecek tiap ~0,8 detik. Server membalas 204 bila tidak ada
 *          perubahan, jadi beban server & jaringan sangat kecil.
 */
export function createChannel(scheduleId, onMessage) {
    const name = `gpi-presenter-${scheduleId}`;
    let bc = null;
    try { bc = new BroadcastChannel(name); } catch { bc = null; }

    if (bc) {
        bc.onmessage = (e) => onMessage(e.data);
    } else {
        window.addEventListener('storage', (e) => {
            if (e.key !== name || !e.newValue) return;
            try { onMessage(JSON.parse(e.newValue).msg); } catch { /* abaikan */ }
        });
    }

    return {
        send(msg) {
            if (bc) { bc.postMessage(msg); return; }
            try { localStorage.setItem(name, JSON.stringify({ msg, t: Date.now() })); } catch { /* abaikan */ }
        },
    };
}

export function jsonHeaders(csrf) {
    return { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' };
}

export function pushServerState(url, csrf, state) {
    return fetch(url, { method: 'POST', headers: jsonHeaders(csrf), body: JSON.stringify(state), keepalive: true })
        .then((r) => r.ok)
        .catch(() => false);
}

/** Polling status server; berhenti otomatis melambat saat tab tersembunyi. */
export function pollServerState(url, onState, onHealth = () => {}, interval = 800) {
    let seq = -1;
    let stopped = false;

    async function tick() {
        try {
            const r = await fetch(`${url}?seq=${seq}`, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (r.status === 200) {
                const state = await r.json();
                seq = state.seq;
                onState(state);
            }
            onHealth(r.ok || r.status === 204);
        } catch {
            onHealth(false);
        }
        if (!stopped) setTimeout(tick, document.hidden ? 3000 : interval);
    }
    tick();
    return () => { stopped = true; };
}
