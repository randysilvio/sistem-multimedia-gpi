<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ \App\Services\Branding::logoUrl() }}">
    <title>Buletin {{ str_replace(' (Custom)', '', $schedule->liturgy->name ?? 'Ibadah') }} {{ \Carbon\Carbon::parse($schedule->worship_date)->format('d-m-Y') }}</title>
    <style>
        :root { --ink: #111; --muted: #555; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #e8eaee; font-family: "Segoe UI", Tahoma, Arial, sans-serif; color: var(--ink); }
        .bar { position: sticky; top: 0; z-index: 5; display: flex; flex-wrap: wrap; align-items: center; gap: 14px; padding: 10px 18px; background: #1d2330; color: #fff; font-size: 14px; }
        .bar strong { margin-right: auto; }
        .bar label { display: inline-flex; align-items: center; gap: 6px; cursor: pointer; }
        .bar select, .bar button { font: inherit; padding: 6px 12px; border-radius: 6px; border: 1px solid #4a5468; background: #2b3344; color: #fff; }
        .bar button.primary { background: #e8663c; border-color: #e8663c; font-weight: 700; }
        .hint { padding: 8px 18px; font-size: 13px; color: #334; background: #fff7e0; border-bottom: 1px solid #f0dca0; }

        /* Halaman A5 (148 x 210 mm) */
        .page { width: 148mm; height: 210mm; padding: 11mm 11mm 12mm; background: #fff; position: relative; overflow: hidden; }
        .page-body { height: 100%; overflow: hidden; font-size: var(--fs, 10pt); line-height: 1.38; }
        .page-no { position: absolute; bottom: 5mm; left: 0; right: 0; text-align: center; font-size: 8pt; color: var(--muted); }
        .sheets { display: flex; flex-direction: column; align-items: center; gap: 10mm; padding: 10mm 0; }
        .sheet { display: flex; background: #fff; box-shadow: 0 4px 18px rgba(0, 0, 0, .18); }
        .sheet .page + .page { border-left: 1px dashed #ccd; }
        .plain .sheet .page + .page { border-left: 0; }

        /* Blok isi */
        .b-cover { text-align: center; margin-bottom: 4mm; }
        .b-cover img { height: 18mm; margin-bottom: 2mm; }
        .b-cover h1 { margin: 0; font-size: 1.25em; letter-spacing: .03em; }
        .b-cover .d { font-weight: 600; }
        .b-cover .t { margin-top: 1.5mm; font-weight: 700; }
        .b-cover .p { color: var(--muted); font-size: .92em; }
        .b-heading { margin: 3.2mm 0 1mm; font-weight: 800; font-size: .98em; letter-spacing: .02em; }
        .b-rubric { text-align: right; font-style: italic; color: var(--muted); margin: 1mm 0; }
        .b-line { display: grid; grid-template-columns: 11mm 1fr; gap: 0 1.5mm; margin: .6mm 0 1.2mm; text-align: justify; }
        .b-line .s { font-weight: 700; }
        .b-line .s::after { content: " :"; }
        .b-line.no-s { grid-template-columns: 1fr; }
        .b-song { margin: 3mm 0 1mm; font-weight: 800; }
        .b-song .code { white-space: nowrap; }
        .b-verse { display: grid; grid-template-columns: 5mm 1fr; margin: .5mm 0 1.4mm 2mm; font-style: italic; white-space: pre-line; }
        .b-verse .reff { margin-top: .6mm; font-weight: 700; }
        .b-verse .reff::before { content: "Reff : "; }
        .b-verse .rmark::after { content: " …Reff"; font-weight: 700; }

        @media print {
            body { background: #fff; }
            .bar, .hint { display: none; }
            .sheets { padding: 0; gap: 0; display: block; }
            .sheet { box-shadow: none; break-after: page; page-break-after: always; }
            .sheet:last-child { break-after: auto; page-break-after: auto; }
            .sheet .page + .page { border-left: 0; }
        }
        @page { margin: 0; }
    </style>
    <style id="page-size">@page { size: A4 landscape; }</style>
</head>
<body>
    <div class="bar">
        <strong>Cetak Buletin Tata Ibadah</strong>
        <label><input type="radio" name="mode" value="booklet" checked> Booklet (A4 dilipat)</label>
        <label><input type="radio" name="mode" value="plain"> Halaman A5 berurutan</label>
        <label>Huruf
            <select id="fs">
                <option value="9pt">Kecil</option>
                <option value="10pt" selected>Sedang</option>
                <option value="11pt">Besar</option>
            </select>
        </label>
        <button type="button" class="primary" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>
    <div class="hint" id="hint"></div>
    <div class="sheets" id="sheets"></div>
    <div id="flow" style="position:absolute;left:-9999px;top:0"></div>

    <script>
        const BLOCKS = @json($blocks);
        const LOGO = @json(\App\Services\Branding::logoUrl());
        const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

        function blockEl(b) {
            const d = document.createElement('div');
            switch (b.t) {
                case 'cover':
                    d.className = 'b-cover';
                    d.innerHTML = `<img src="${esc(LOGO)}" alt="" onerror="this.remove()"><h1>${esc(b.title)}</h1><div class="d">${esc(b.date)}</div>`
                        + (b.theme ? `<div class="t">TEMA: ${esc(String(b.theme).toUpperCase())}</div>` : '')
                        + (b.preacher ? `<div class="p">Pelayan Firman: ${esc(b.preacher)}</div>` : '');
                    break;
                case 'heading': d.className = 'b-heading'; d.textContent = b.text; break;
                case 'rubric': d.className = 'b-rubric'; d.textContent = b.text; break;
                case 'line':
                    d.className = 'b-line' + (b.speaker ? '' : ' no-s');
                    d.innerHTML = (b.speaker ? `<span class="s">${esc(b.speaker)}</span>` : '') + `<span>${esc(b.text)}</span>`;
                    break;
                case 'song':
                    d.className = 'b-song';
                    d.innerHTML = `${esc(b.label)} : <span class="code">${esc(b.code)}${b.verses ? ' : ' + esc(b.verses) : ''}</span>${b.title ? ' “' + esc(String(b.title).toUpperCase()) + '”' : ''}`;
                    d.dataset.keep = '1';
                    break;
                case 'verse':
                    d.className = 'b-verse';
                    d.innerHTML = `<span>${esc(b.n)}.</span><span class="${b.reffMark ? 'rmark' : ''}">${esc(b.text)}${b.reff ? `<div class="reff">${esc(b.reff)}</div>` : ''}</span>`;
                    break;
            }
            if (b.t === 'heading') d.dataset.keep = '1';
            return d;
        }

        function newPage() {
            const p = document.createElement('div');
            p.className = 'page';
            p.innerHTML = '<div class="page-body"></div><div class="page-no"></div>';
            return p;
        }

        /** Isi blok ke halaman A5; judul tidak boleh tertinggal sendirian di bawah halaman. */
        function paginate() {
            const flow = document.getElementById('flow');
            flow.replaceChildren();
            const pages = [newPage()];
            flow.appendChild(pages[0]);
            BLOCKS.forEach((b) => {
                const el = blockEl(b);
                let page = pages[pages.length - 1];
                let body = page.firstElementChild;
                body.appendChild(el);
                if (body.scrollHeight > body.clientHeight + 1 && body.children.length > 1) {
                    body.removeChild(el);
                    const carry = [];
                    while (body.lastElementChild && body.lastElementChild.dataset.keep && body.children.length > 1) carry.unshift(body.removeChild(body.lastElementChild));
                    page = newPage();
                    flow.appendChild(page);
                    pages.push(page);
                    body = page.firstElementChild;
                    carry.forEach((c) => body.appendChild(c));
                    body.appendChild(el);
                }
            });
            return pages;
        }

        /** Urutan booklet untuk sisi lembar ke-i dari total H halaman: [8|1], [2|7], [6|3], [4|5]. */
        function bookletOrder(i, total) {
            return i % 2 === 0 ? [total - i, i + 1] : [i + 1, total - i];
        }

        function render() {
            const mode = document.querySelector('input[name=mode]:checked').value;
            document.documentElement.style.setProperty('--fs', document.getElementById('fs').value);
            const pages = paginate();
            pages.forEach((p, i) => { p.querySelector('.page-no').textContent = i + 1; });
            const sheets = document.getElementById('sheets');
            sheets.replaceChildren();
            sheets.classList.toggle('plain', mode === 'plain');
            document.getElementById('page-size').textContent = mode === 'plain' ? '@page { size: A5 portrait; }' : '@page { size: A4 landscape; }';

            if (mode === 'plain') {
                pages.forEach((p) => { const s = document.createElement('div'); s.className = 'sheet'; s.appendChild(p); sheets.appendChild(s); });
                document.getElementById('hint').textContent = `${pages.length} halaman A5. Atur kertas printer ke A5, atau pilih "Simpan sebagai PDF".`;
                return;
            }
            while (pages.length % 4) { const blank = newPage(); pages.push(blank); }
            const total = pages.length;
            for (let i = 0; i < total / 2; i++) {
                const [l, r] = bookletOrder(i, total);
                const s = document.createElement('div');
                s.className = 'sheet';
                s.append(pages[l - 1], pages[r - 1]);
                sheets.appendChild(s);
            }
            document.getElementById('hint').textContent = `${total} halaman A5 di ${total / 4} lembar A4. Cetak bolak-balik dengan pilihan "balik pada sisi pendek", lalu lipat dua.`;
        }

        document.querySelectorAll('input[name=mode]').forEach((r) => r.addEventListener('change', render));
        document.getElementById('fs').addEventListener('change', render);
        (document.fonts ? document.fonts.ready : Promise.resolve()).then(render);
    </script>
</body>
</html>
