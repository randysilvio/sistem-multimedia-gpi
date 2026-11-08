import { jsonHeaders } from './sync.js';

/**
 * Panel edit isi slide di control panel.
 * Auto-save 1,2 detik setelah berhenti mengetik; server langsung membalas
 * daftar slide terbaru (JSON): tidak ada lagi unduh ulang halaman penuh.
 */
export function initEditor({ form, csrf, routes, onSaved, onStatus }) {
    let saveTimer = null;
    let requestNo = 0;

    // ---- Urungkan (Ctrl+Z): simpan keadaan form sebelum tiap penyimpanan ----
    const history = [];
    let skipHistory = false;
    function snapshot() {
        form.querySelectorAll('textarea').forEach((t) => { t.textContent = t.value; });
        form.querySelectorAll('input').forEach((i) => {
            if (i.type === 'checkbox' || i.type === 'radio') i.toggleAttribute('checked', i.checked);
            else i.setAttribute('value', i.value);
        });
        form.querySelectorAll('select').forEach((sel) => [...sel.options].forEach((o) => o.toggleAttribute('selected', o.selected)));
        return form.innerHTML;
    }
    let saved = snapshot();

    /** Kembalikan isi ke keadaan sebelumnya lalu simpan. false = tidak ada yang bisa diurungkan. */
    function undo() {
        clearTimeout(saveTimer);
        const current = snapshot();
        const target = current !== saved ? saved : history.pop();
        if (!target) return false;
        form.innerHTML = target;
        skipHistory = true;
        save();
        return true;
    }

    function scheduleSave(delay = 1200) {
        clearTimeout(saveTimer);
        onStatus('pending');
        saveTimer = setTimeout(save, delay);
    }

    async function save() {
        clearTimeout(saveTimer);
        const mine = ++requestNo;
        const now = snapshot();
        if (!skipHistory && now !== saved) {
            history.push(saved);
            if (history.length > 30) history.shift();
        }
        skipHistory = false;
        saved = now;
        onStatus('saving');
        try {
            const r = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
            });
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            const data = await r.json();
            if (mine !== requestNo) return; // ada simpanan yang lebih baru
            onSaved(data);
            onStatus('saved');
        } catch (err) {
            console.warn('Gagal menyimpan:', err);
            if (mine === requestNo) onStatus('error');
        }
    }

    form.addEventListener('input', (e) => {
        if (e.target.classList.contains('ed-bait-label')) return; // diproses saat 'change'
        scheduleSave();
    });
    form.addEventListener('change', (e) => {
        if (e.target.classList.contains('ed-bait-label')) renameBait(e.target);
        scheduleSave(300);
    });
    form.addEventListener('submit', (e) => { e.preventDefault(); save(); });

    form.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const item = btn.dataset.item;
        switch (btn.dataset.action) {
            case 'hapus':
                btn.closest('.ed-bait, .ed-custom')?.remove();
                scheduleSave(300);
                break;
            case 'tambah-bait': {
                const box = document.getElementById(`bait-container-${item}`);
                const row = baitRow(item, String(nextVerseNumber(box)), '');
                box.appendChild(row);
                row.querySelector('textarea').focus();
                break;
            }
            case 'tambah-slide':
                document.getElementById(`custom-slide-container-${item}`).appendChild(customRow(item));
                break;
            case 'tarik-lagu':
                fetchSong(item, btn);
                break;
            case 'tarik-alkitab':
                fetchBible(item, btn);
                break;
        }
    });

    // ---------- Bait lagu ----------
    function nextVerseNumber(box) {
        let max = 0;
        box.querySelectorAll('.ed-bait-label').forEach((l) => { const n = parseInt(l.value, 10); if (n > max) max = n; });
        return max + 1;
    }

    function uniqueKey(box, wanted, except) {
        const used = new Set([...box.querySelectorAll('textarea')].filter((t) => t !== except)
            .map((t) => (t.name.match(/\[bait\]\[(.*?)\]$/) || [])[1]));
        let key = wanted;
        let n = 2;
        while (used.has(key)) key = `${wanted}_${n++}`;
        return key;
    }

    function baitRow(item, label, text, isReff = false) {
        const box = document.getElementById(`bait-container-${item}`);
        const row = document.createElement('div');
        row.className = 'ed-bait';
        const input = document.createElement('input');
        input.className = 'ed-bait-label' + (isReff ? ' is-reff' : '');
        input.value = isReff ? 'Reff' : label;
        input.title = "Ketik angka bait, atau 'Reff'";
        const ta = document.createElement('textarea');
        ta.className = 'ed-input';
        ta.rows = 3;
        ta.value = text;
        const key = uniqueKey(box, isReff ? `ref_${Math.random().toString(36).slice(2, 7)}` : (label.replace(/[^A-Za-z0-9]/g, '') || 'b'));
        ta.name = `dynamic_content[${item}][bait][${key}]`;
        const x = document.createElement('button');
        x.type = 'button';
        x.className = 'ed-x';
        x.dataset.action = 'hapus';
        x.title = 'Hapus bait';
        x.textContent = '×';
        row.append(input, ta, x);
        return row;
    }

    function renameBait(input) {
        const row = input.closest('.ed-bait');
        const box = row.parentElement;
        const ta = row.querySelector('textarea');
        const item = box.dataset.item;
        const current = (ta.name.match(/\[bait\]\[(.*?)\]$/) || [])[1] || '';
        const label = input.value.trim() || 'Bait';
        const isReff = /^ref/i.test(label);
        input.classList.toggle('is-reff', isReff);
        if (isReff) input.value = 'Reff';
        let wanted = isReff ? (/ref/i.test(current) ? current : `ref_${Math.random().toString(36).slice(2, 7)}`) : (label.replace(/[^A-Za-z0-9]/g, '') || 'b');
        ta.name = `dynamic_content[${item}][bait][${uniqueKey(box, wanted, ta)}]`;
    }

    function customRow(item) {
        const id = 'n' + Math.random().toString(36).slice(2, 9);
        const wrap = document.createElement('div');
        wrap.className = 'ed-custom';
        wrap.innerHTML = `
            <input class="ed-input" placeholder="Judul slide ekstra">
            <textarea class="ed-input" rows="2" placeholder="Isi teks"></textarea>
            <div class="ed-row ed-row--between">
                <label class="ed-switch"><input type="hidden" value="0"><input type="checkbox" value="1"><span>Kamera</span></label>
                <button type="button" class="ed-link-danger" data-action="hapus">Hapus</button>
            </div>`;
        const base = `custom_slides[${item}][${id}]`;
        wrap.querySelector('input.ed-input').name = `${base}[title]`;
        wrap.querySelector('textarea').name = `${base}[content]`;
        wrap.querySelectorAll('.ed-switch input').forEach((i) => { i.name = `${base}[use_camera]`; });
        return wrap;
    }

    // ---------- Tarik data lagu & Alkitab ----------
    async function withBusy(btn, fn) {
        const text = btn.textContent;
        btn.textContent = '…';
        btn.disabled = true;
        try { await fn(); } finally { btn.textContent = text; btn.disabled = false; }
    }

    function fetchSong(item, btn) {
        const book = document.getElementById(`buku-lagu-${item}`).value;
        const number = document.getElementById(`nomor-lagu-${item}`).value.trim();
        if (!number) { alert('Ketik nomor nyanyian.'); return; }
        withBusy(btn, async () => {
            try {
                const r = await fetch(`${routes.lagu}?buku=${encodeURIComponent(book)}&nomor=${encodeURIComponent(number)}`, { headers: jsonHeaders(csrf) });
                const data = await r.json();
                if (!data.success) { alert(data.message); return; }
                form.querySelector(`input[name="dynamic_content[${item}][judul]"]`).value = data.judul;
                const box = document.getElementById(`bait-container-${item}`);
                box.replaceChildren();
                // `labels` = nomor bait asli dari database (mis. 1, 7), sejajar dengan potongan teks
                const labels = Array.isArray(data.labels) ? data.labels : [];
                let verse = 1;
                data.text.split('===SLIDE_BREAK===').forEach((piece, idx) => {
                    const raw = piece.trim();
                    if (!raw) return;
                    const isReff = /^\[?reff?\]?/i.test(raw);
                    const clean = raw.replace(/^\[?REFF\]?\s*/i, '');
                    const label = labels[idx] && labels[idx] !== 'Reff' ? String(labels[idx]) : String(verse);
                    box.appendChild(baitRow(item, label, clean, isReff));
                    if (!isReff) verse++;
                });
                save();
            } catch {
                alert('Data lagu tidak ditemukan.');
            }
        });
    }

    // ---------- Alkitab ----------
    function bibleStatus(item, text, isError = false) {
        const el = document.getElementById(`alkitab-status-${item}`);
        if (!el) { if (isError) alert(text); return; }
        el.textContent = text;
        el.classList.toggle('is-error', isError);
        el.classList.toggle('is-ok', !isError);
    }

    function fetchBible(item, btn) {
        const input = document.getElementById(`input-alkitab-${item}`);
        const query = input.value.trim();
        if (!query) { bibleStatus(item, 'Ketik referensi ayat. Contoh: Yohanes 3:16-18', true); input.focus(); return; }
        withBusy(btn, async () => {
            try {
                const r = await fetch(`${routes.alkitab}?q=${encodeURIComponent(query)}`, { headers: jsonHeaders(csrf) });
                const data = await r.json();
                if (!data.success) { bibleStatus(item, data.message, true); input.focus(); return; }
                input.value = data.reference; // dirapikan server: "yoh 3 : 16 - 18" -> "Yohanes 3:16-18"
                document.getElementById(`textarea-${item}`).value = data.text;
                bibleStatus(item, `${data.reference} · ${data.count} ayat · ${data.version}`);
                save();
            } catch {
                bibleStatus(item, 'Gangguan jaringan. Coba lagi.', true);
            }
        });
    }

    // Enter di kolom referensi = Tarik (bukan submit form)
    form.addEventListener('keydown', (e) => {
        const item = e.target.dataset?.bibleInput;
        if (!item || e.key !== 'Enter') return;
        e.preventDefault();
        fetchBible(item, form.querySelector(`[data-action="tarik-alkitab"][data-item="${item}"]`));
    });

    return { save, undo };
}
