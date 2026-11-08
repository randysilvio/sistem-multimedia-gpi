/*
 * Kotak "Pilih Lagu": nomor-nomor lagu yang sudah ada di database, ditata seperti kalender per buku.
 * Dipakai di Builder, Buat Jadwal, dan Edit Jadwal.
 *
 * Markup yang dibutuhkan (satu baris referensi lagu):
 *   <div class="song-ref">
 *     <select>…buku…</select> <input …nomor…> <button data-song-pull>Tarik</button>
 *     <button type="button" data-song-picker>Pilih</button>
 *   </div>
 * Klik sebuah nomor → buku & nomor terisi, lalu tombol Tarik ditekan otomatis.
 */
const BOOK_NAMES = { KJ: 'KJ', NKB: 'NKB', PKJ: 'PKJ', NR: 'NR', DSL: 'DSL', GPM: 'GPM', BEBAS: 'Lainnya' };

let songsPromise = null;
let modal = null;
let el = null;
let target = null;
let activeBook = null;
let current = null; // lagu yang sedang terisi di slot, mis. {book:'PKJ', number:'11'}

function loadSongs(url) {
    songsPromise ??= fetch(url, { headers: { Accept: 'application/json' } })
        .then((r) => (r.ok ? r.json() : Promise.reject(new Error(r.status))))
        .then((d) => d.songs || [])
        .catch((e) => { songsPromise = null; throw e; });
    return songsPromise;
}

function build() {
    el = document.createElement('div');
    el.className = 'modal fade';
    el.tabIndex = -1;
    el.setAttribute('aria-labelledby', 'sp-title');
    el.innerHTML = `
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content sp">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="sp-title">Pilih Lagu dari Database</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="sp-bar">
                    <div class="sp-books" role="tablist" aria-label="Buku nyanyian"></div>
                    <input type="search" class="form-control form-control-sm sp-search" placeholder="Cari judul atau nomor" aria-label="Cari lagu">
                </div>
                <div class="modal-body">
                    <p class="sp-state adm-muted mb-0">Memuat daftar lagu…</p>
                    <div class="sp-grid" role="list"></div>
                </div>
                <div class="modal-footer sp-foot">
                    <span class="adm-muted small">Hanya lagu yang sudah tersimpan di Database Lagu. Kotak bergaris putus-putus: lirik belum lengkap.</span>
                </div>
            </div>
        </div>`;
    document.body.appendChild(el);
    modal = new window.bootstrap.Modal(el);

    el.querySelector('.sp-search').addEventListener('input', () => render());
    el.querySelector('.sp-books').addEventListener('click', (e) => {
        const b = e.target.closest('[data-book]');
        if (!b) return;
        activeBook = b.dataset.book;
        el.querySelector('.sp-search').value = '';
        render();
    });
    el.querySelector('.sp-grid').addEventListener('click', (e) => {
        const t = e.target.closest('[data-number]');
        if (t) pick(t.dataset.book, t.dataset.number);
    });
    el.addEventListener('shown.bs.modal', () => el.querySelector('.sp-search').focus());
}

let allSongs = [];

function render() {
    const q = el.querySelector('.sp-search').value.trim().toLowerCase();
    const books = [...new Set(allSongs.map((s) => s.book))];
    if (!books.includes(activeBook)) activeBook = books[0] ?? null;

    const tabs = el.querySelector('.sp-books');
    tabs.replaceChildren(...books.map((book) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.dataset.book = book;
        b.setAttribute('role', 'tab');
        const on = !q && book === activeBook;
        b.className = `sp-book${on ? ' is-active' : ''}`;
        b.setAttribute('aria-selected', on ? 'true' : 'false');
        b.textContent = BOOK_NAMES[book] ?? book;
        const n = document.createElement('small');
        n.textContent = allSongs.filter((s) => s.book === book).length;
        b.appendChild(n);
        return b;
    }));

    // Dengan kata kunci: cari di semua buku. Tanpa kata kunci: buku yang dipilih.
    const list = q
        ? allSongs.filter((s) => s.title.toLowerCase().includes(q) || s.number.toLowerCase() === q || `${s.book} ${s.number}`.toLowerCase() === q)
        : allSongs.filter((s) => s.book === activeBook);

    const grid = el.querySelector('.sp-grid');
    grid.replaceChildren(...list.map((s) => {
        const t = document.createElement('button');
        t.type = 'button';
        const isCurrent = current && current.book === s.book && current.number === s.number;
        t.className = `sp-tile${s.incomplete ? ' is-incomplete' : ''}${isCurrent ? ' is-current' : ''}`;
        if (isCurrent) t.setAttribute('aria-current', 'true');
        t.dataset.book = s.book;
        t.dataset.number = s.number;
        t.setAttribute('role', 'listitem');
        t.title = `${BOOK_NAMES[s.book] ?? s.book} ${s.number} - ${s.title}${s.incomplete ? ' (lirik belum lengkap)' : ''}`;
        const num = document.createElement('b');
        num.textContent = s.book === 'BEBAS' ? '•' : s.number;
        const title = document.createElement('span');
        title.textContent = s.title;
        t.append(num, title);
        if (q) {
            const book = document.createElement('small');
            book.textContent = BOOK_NAMES[s.book] ?? s.book;
            t.prepend(book);
        }
        return t;
    }));

    const state = el.querySelector('.sp-state');
    state.hidden = list.length > 0;
    state.textContent = q ? `Tidak ada lagu dengan kata "${q}".` : 'Belum ada lagu di buku ini.';
}

function pick(book, number) {
    if (!target) return;
    const select = target.querySelector('select');
    const input = target.querySelector('input[type="text"], input:not([type])');
    if (select) {
        if (![...select.options].some((o) => o.value === book)) select.add(new Option(BOOK_NAMES[book] ?? book, book));
        select.value = book;
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (input) {
        input.value = number;
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }
    modal.hide();
    target.querySelector('[data-song-pull]')?.click();
}

async function open(row, url) {
    target = row;
    if (!el) build();
    // Buka di buku lagu yang sudah terisi (judul "PKJ 11 - ..."); bila kosong, buku yang dipilih
    const judul = row.parentElement?.querySelector('input[name$="[judul]"]')?.value || '';
    const m = judul.match(/^\s*([A-Z]+)\s+(\S+)\s*-/);
    current = m && BOOK_NAMES[m[1]] ? { book: m[1], number: m[2] } : null;
    activeBook = current?.book || row.querySelector('select')?.value || activeBook;
    el.querySelector('.sp-search').value = '';
    modal.show();
    const state = el.querySelector('.sp-state');
    if (!allSongs.length) {
        state.hidden = false;
        state.textContent = 'Memuat daftar lagu…';
        try {
            allSongs = await loadSongs(url);
        } catch {
            state.textContent = 'Daftar lagu gagal dimuat. Periksa koneksi lalu coba lagi.';
            return;
        }
    }
    render();
}

export function setupSongPicker() {
    const url = document.querySelector('meta[name="song-list-url"]')?.content;
    if (!url) return;
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-song-picker]');
        if (!btn) return;
        const row = btn.closest('.song-ref');
        if (row) open(row, url);
    });
}
