/*
 * "Buat dari Buletin": baca teks PDF tata ibadah langsung di browser (pdf.js),
 * termasuk PDF BOOKLET (A4 mendatar, 2 halaman per sisi, urutan halaman diacak
 * untuk dilipat): halaman disusun ulang ke urutan baca 1, 2, 3, ...
 */
import * as pdfjs from 'pdfjs-dist/build/pdf.min.mjs';
import workerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

pdfjs.GlobalWorkerOptions.workerSrc = workerUrl;

/** Susun potongan teks menjadi baris (urut dari atas ke bawah, kiri ke kanan). */
function toLines(items) {
    const rows = [];
    items.sort((a, b) => b.y - a.y || a.x - b.x).forEach((it) => {
        const row = rows.find((r) => Math.abs(r.y - it.y) <= 3);
        if (row) row.items.push(it); else rows.push({ y: it.y, items: [it] });
    });
    return rows
        .sort((a, b) => b.y - a.y)
        .map((r) => {
            const parts = r.items.sort((a, b) => a.x - b.x);
            let line = '';
            parts.forEach((p, i) => {
                const prev = parts[i - 1];
                const gap = prev ? p.x - (prev.x + prev.w) : 0;
                line += (prev && gap > 1.5 && !line.endsWith(' ') && !p.str.startsWith(' ') ? ' ' : '') + p.str;
            });
            return line.replace(/\s+/g, ' ').trim();
        })
        .filter(Boolean)
        .join('\n');
}

/**
 * Urutan halaman booklet: lembar ke-i (0-based) dari total H halaman lipat.
 * Contoh 8 halaman: [8|1], [2|7], [6|3], [4|5].
 */
export function bookletOrder(sheetIndex, totalSheets) {
    const H = totalSheets * 2;
    return sheetIndex % 2 === 0 ? [H - sheetIndex, sheetIndex + 1] : [sheetIndex + 1, H - sheetIndex];
}

async function readPdf(file, booklet) {
    const pdf = await pdfjs.getDocument({ data: await file.arrayBuffer() }).promise;
    const parts = [];
    let landscape = 0;
    let pageCount = pdf.numPages;
    // Halaman kosong ekstra di akhir (sering muncul saat mencetak ke PDF) merusak hitungan booklet
    if (booklet && pageCount > 1 && pageCount % 2 === 1) {
        const last = await (await pdf.getPage(pageCount)).getTextContent();
        if (!last.items.some((i) => i.str && i.str.trim())) pageCount--;
    }
    for (let p = 1; p <= pageCount; p++) {
        const page = await pdf.getPage(p);
        const vp = page.getViewport({ scale: 1 });
        if (vp.width > vp.height) landscape++;
        const content = await page.getTextContent();
        const items = content.items
            .filter((i) => i.str && i.str.trim())
            .map((i) => ({ x: i.transform[4], y: i.transform[5], w: i.width || 0, str: i.str }));
        if (booklet && vp.width > vp.height) {
            const mid = vp.width / 2;
            const [lo, ro] = bookletOrder(p - 1, pageCount);
            parts.push({ order: lo, text: toLines(items.filter((i) => i.x < mid)) });
            parts.push({ order: ro, text: toLines(items.filter((i) => i.x >= mid)) });
        } else {
            parts.push({ order: p, text: toLines(items) });
        }
    }
    return {
        text: parts.sort((a, b) => a.order - b.order).map((x) => x.text).filter(Boolean).join('\n\n'),
        pages: pageCount,
        landscape,
    };
}

const input = document.getElementById('pdf-file');
const status = document.getElementById('pdf-status');
const textarea = document.getElementById('bulletin-text');
const bookletBox = document.getElementById('pdf-booklet');

async function run() {
    const file = input?.files?.[0];
    if (!file) return;
    status.className = 'small mt-2 text-muted';
    status.textContent = 'Membaca PDF…';
    try {
        let res = await readPdf(file, bookletBox.checked);
        // Deteksi otomatis: semua halaman mendatar = kemungkinan besar booklet
        if (!bookletBox.dataset.touched && !bookletBox.checked && res.landscape === res.pages && res.pages > 1) {
            bookletBox.checked = true;
            res = await readPdf(file, true);
        }
        if (!res.text.trim()) throw new Error('PDF tidak berisi teks (hasil scan/gambar). Ketik atau tempel teksnya.');
        textarea.value = res.text;
        status.className = 'small mt-2 text-success';
        status.textContent = `Teks dari ${res.pages} halaman PDF${bookletBox.checked ? ' (disusun ulang dari booklet)' : ''} sudah dimasukkan. Periksa sekilas, lalu klik "Periksa hasil".`;
    } catch (err) {
        status.className = 'small mt-2 text-danger';
        status.textContent = `Gagal membaca PDF: ${err.message}`;
    }
}

input?.addEventListener('change', run);
bookletBox?.addEventListener('change', () => { bookletBox.dataset.touched = '1'; run(); });
