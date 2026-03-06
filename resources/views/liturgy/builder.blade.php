<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Builder Presentasi Kustom - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; padding-bottom: 100px; color:#1e293b; }
        .navbar { background-color: #0f172a !important; }
        .navbar-brand { font-weight: 700; letter-spacing: 1px; font-size: 1.1rem; }
        
        .builder-container { max-width: 800px; margin: auto; margin-top: 30px; }
        
        .block-wrapper { position: relative; }
        .block-card { background: white; border-radius: 8px; padding: 25px; border: 1px solid #e2e8f0; position: relative; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: 0.2s; }
        .block-card:focus-within { box-shadow: 0 4px 15px rgba(43, 108, 176, 0.15); border-color: #90cdf4; }
        
        .btn-delete-block { position: absolute; top: 15px; right: 15px; background: transparent; border: 1px solid #e2e8f0; color: #ef4444; font-size: 1.2rem; cursor: pointer; width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; transition: 0.2s; line-height: 1; }
        .btn-delete-block:hover { background: #fee2e2; border-color: #fca5a5; transform: scale(1.05); }
        
        /* DESAIN GARIS SISIPKAN (ELEGAN) */
        .insert-divider { position: relative; height: 36px; display: flex; align-items: center; justify-content: center; margin-top: -12px; margin-bottom: -12px; z-index: 10; opacity: 0; transition: opacity 0.3s; }
        .block-wrapper:hover .insert-divider, .insert-divider:hover, .insert-divider.show { opacity: 1; z-index: 950; }
        .top-divider { opacity: 1; margin-bottom: 15px; margin-top: 0; height: 40px; }
        .insert-divider::before { content: ""; position: absolute; width: 100%; height: 2px; background: #cbd5e1; z-index: 1; border-radius: 2px; transition: 0.2s; }
        .block-wrapper:hover .insert-divider::before { background: #94a3b8; }
        
        .btn-insert { position: relative; z-index: 2; background: #ffffff; color: #475569; border: 1px solid #cbd5e1; border-radius: 50%; width: 34px; height: 34px; font-weight: 400; font-size: 22px; display: flex; align-items: center; justify-content: center; padding: 0; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: 0.2s; line-height: 1; }
        .btn-insert:hover, .btn-insert[aria-expanded="true"] { border-color: #2b6cb0; color: #2b6cb0; background: #ebf8ff; transform: scale(1.1); box-shadow: 0 4px 10px rgba(43, 108, 176, 0.15); }
        
        /* DROPDOWN KUSTOM */
        .dropdown-menu { border-radius: 8px; padding: 8px 0; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 1050 !important; min-width: 220px; }
        .dropdown-item { padding: 10px 20px; font-size: 0.85rem; font-weight: 500; color: #334155; }
        .dropdown-item:hover { background-color: #f8fafc; color: #0f172a; }
        .dropdown-divider { border-top: 1px solid #e2e8f0; margin: 5px 0; }

        /* SOLID BOTTOM TOOLBAR PROFESIONAL */
        .toolbar-menu { position: fixed; bottom: 0; left: 0; width: 100%; background: #ffffff; padding: 15px 0; border-top: 1px solid #e2e8f0; box-shadow: 0 -4px 20px rgba(0,0,0,0.04); z-index: 900; }
        
        .btn-primary-custom { background-color: #0f172a; color: white; border: none; font-weight: 600; letter-spacing: 0.5px; padding: 10px 30px; border-radius: 6px; }
        .btn-primary-custom:hover { background-color: #1e293b; color: white; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm py-3 mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-uppercase m-0 d-flex align-items-center" href="{{ route('liturgy.gallery') }}">
                <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" alt="Logo GPI" height="32" class="me-3">
                <span>Builder Presentasi Kustom</span>
            </a>
            <a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-light btn-sm fw-medium px-4">Batal & Kembali</a>
        </div>
    </nav>

    <div class="container builder-container">
        <form action="{{ route('liturgy.store_custom') }}" method="POST">
            @csrf
            
            <div class="card border-0 shadow-sm mb-4 rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3 text-uppercase" style="letter-spacing: 1px; font-size:0.85rem;">Informasi Dasar Ibadah</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Nama Jadwal / Tema</label>
                            <input type="text" name="schedule_name" class="form-control bg-light" placeholder="Cth: Ibadah Wadah Laki-Laki" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Tanggal Ibadah</label>
                            <input type="date" name="worship_date" class="form-control bg-light" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-secondary">Pelayan Firman</label>
                            <input type="text" name="preacher_name" class="form-control bg-light" placeholder="Nama Pendeta / Pelayan Firman">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Warna Tema Utama</label>
                            <input type="color" name="theme_color" class="form-control form-control-color w-100 bg-light" value="#1b2735">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-end mt-5 mb-3">
                <h6 class="fw-bold text-dark text-uppercase m-0" style="letter-spacing: 1px; font-size:0.85rem;">Struktur Slide Presentasi</h6>
                <small class="text-muted">Arahkan kursor di antara slide untuk menyisipkan</small>
            </div>
            
            <div id="canvas-area">
                <div class="insert-divider top-divider" id="top-anchor">
                    <div class="dropdown">
                        <button type="button" class="btn-insert" data-bs-toggle="dropdown" aria-expanded="false" title="Tambah Slide Paling Atas">&plus;</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('nyanyian', 'top')">Tambah Nyanyian Jemaat</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('alkitab', 'top')">Tambah Bacaan Alkitab</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('votum', 'top')">Tambah Votum / Prosesi</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('aksi', 'top')">Tambah Instruksi Sikap Jemaat</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('polos', 'top')">Tambah Slide Teks Bebas</a></li>
                        </ul>
                    </div>
                </div>
                
                </div>

            <div class="toolbar-menu">
                <div class="container d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary-custom">SIMPAN & RAKIT PRESENTASI</button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let blockCount = 0;

        function insertBlock(type, referenceId) {
            blockCount++;
            const newId = blockCount;
            let cardColor = '';
            let typeLabel = '';
            let contentHtml = '';

            // Palette Warna Elegan
            if (type === 'nyanyian') {
                cardColor = '#2b6cb0'; typeLabel = 'NYANYIAN JEMAAT';
                contentHtml = `
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-muted">Keterangan / Momen</label>
                        <input type="text" name="blocks[${newId}][title]" class="form-control fw-bold" placeholder="Cth: Nyanyian Persiapan" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small fw-bold text-muted">Tarik Data Lagu</label>
                        <div class="input-group mb-2">
                            <select id="buku-${newId}" class="form-select bg-light" style="max-width: 90px;">
                                <option value="KJ">KJ</option><option value="NKB">NKB</option><option value="PKJ">PKJ</option><option value="NR">NR</option><option value="BEBAS">Lainnya</option>
                            </select>
                            <input type="text" id="nomor-${newId}" class="form-control" placeholder="No. Lagu">
                            <button type="button" class="btn btn-secondary px-3" onclick="tarikLaguBuilder(${newId}, event)">Tarik</button>
                        </div>
                        <input type="text" id="judul-${newId}" class="form-control mb-2 text-primary fw-medium" placeholder="Judul otomatis terdeteksi..." readonly style="font-size: 0.85rem;">
                        
                        <div id="bait-container-${newId}" class="mt-3">
                            <div class="input-group mb-2 position-relative">
                                <span class="input-group-text bg-light text-secondary fw-bold">Bait 1</span>
                                <textarea name="blocks[${newId}][content][]" class="form-control" rows="2" placeholder="Isi lirik..."></textarea>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" style="font-size:0.75rem; font-weight:600;" onclick="tambahBaitLagu(${newId})">+ Tambah Bait Manual</button>
                    </div>
                `;
            } 
            else if (type === 'alkitab') {
                cardColor = '#2c5282'; typeLabel = 'BACAAN ALKITAB';
                contentHtml = `
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-muted">Momen Bacaan</label>
                        <input type="text" name="blocks[${newId}][title]" class="form-control fw-bold" value="Pelayanan Firman" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small fw-bold text-muted">Cari Kitab & Pasal</label>
                        <div class="input-group mb-2">
                            <input type="text" id="cari-kitab-${newId}" class="form-control" placeholder="Cth: Yohanes 3:16">
                            <button type="button" class="btn btn-secondary px-3" onclick="tarikAyatBuilder(${newId}, event)">Tarik</button>
                        </div>
                        <textarea name="blocks[${newId}][content][]" id="text-kitab-${newId}" class="form-control bg-light" rows="4" placeholder="Teks ayat..."></textarea>
                    </div>
                `;
            }
            else if (type === 'votum') {
                cardColor = '#4a5568'; typeLabel = 'VOTUM / PROSESI / PENGAKUAN';
                contentHtml = `
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Judul Sesi</label>
                        <input type="text" name="blocks[${newId}][title]" class="form-control fw-bold" placeholder="Cth: Votum & Salam" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-bold text-muted">Teks Pembacaan</label>
                        <textarea name="blocks[${newId}][content][]" class="form-control" rows="3" placeholder="Ketik teks yang akan dibaca..."></textarea>
                    </div>
                `;
            }
            else if (type === 'aksi') {
                cardColor = '#c53030'; typeLabel = 'INSTRUKSI SIKAP JEMAAT';
                contentHtml = `
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-muted">Keterangan Internal</label>
                        <input type="text" name="blocks[${newId}][title]" class="form-control fw-bold text-muted" value="Instruksi Sikap Jemaat" readonly>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small fw-bold text-muted">Pilih Instruksi Tayang</label>
                        <select name="blocks[${newId}][content][]" class="form-select fw-bold border-secondary text-dark">
                            <option value="(Jemaat Berdiri)">(Jemaat Berdiri)</option>
                            <option value="(Jemaat Duduk)">(Jemaat Duduk)</option>
                            <option value="(Saat Teduh / Lilin Dipadamkan)">(Saat Teduh / Lilin Dipadamkan)</option>
                        </select>
                    </div>
                `;
            }
            else {
                cardColor = '#718096'; typeLabel = 'SLIDE TEKS BEBAS';
                contentHtml = `
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Judul Slide (Header)</label>
                        <input type="text" name="blocks[${newId}][title]" class="form-control fw-bold" placeholder="Judul besar..." required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-bold text-muted">Isi Konten Teks</label>
                        <textarea name="blocks[${newId}][content][]" class="form-control" rows="3" placeholder="Ketik teks bebas..."></textarea>
                    </div>
                `;
            }

            const blockHtml = `
                <div class="block-card" style="border-top: 4px solid ${cardColor};">
                    <input type="hidden" name="blocks[${newId}][type]" value="${type}">
                    <button type="button" class="btn-delete-block" onclick="this.closest('.block-wrapper').remove()" title="Hapus Slide">&times;</button>
                    <div class="mb-3 pb-2">
                        <span class="badge text-uppercase" style="background-color: ${cardColor}; letter-spacing: 0.5px;">${typeLabel}</span>
                    </div>
                    <div class="row g-3">
                        ${contentHtml}
                    </div>
                </div>
            `;

            const dividerHtml = `
                <div class="insert-divider">
                    <div class="dropdown">
                        <button type="button" class="btn-insert" data-bs-toggle="dropdown" aria-expanded="false" title="Sisipkan Slide Di Sini">&plus;</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('nyanyian', '${newId}')">Tambah Nyanyian Jemaat</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('alkitab', '${newId}')">Tambah Bacaan Alkitab</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('votum', '${newId}')">Tambah Votum / Prosesi</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('aksi', '${newId}')">Tambah Instruksi Sikap</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('polos', '${newId}')">Tambah Slide Teks Bebas</a></li>
                        </ul>
                    </div>
                </div>
            `;

            const wrapperHtml = `<div class="block-wrapper" id="wrapper-${newId}">${blockHtml}${dividerHtml}</div>`;

            if (referenceId === 'top') {
                document.getElementById('top-anchor').insertAdjacentHTML('afterend', wrapperHtml);
            } else {
                document.getElementById('wrapper-' + referenceId).insertAdjacentHTML('afterend', wrapperHtml);
            }
            
            document.getElementById('wrapper-' + newId).scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function tambahBaitLagu(blockId) {
            const container = document.getElementById(`bait-container-${blockId}`);
            const baitNum = container.children.length + 1;
            const html = `
                <div class="input-group mb-2 position-relative">
                    <span class="input-group-text bg-light text-secondary fw-bold">Bait ${baitNum}</span>
                    <textarea name="blocks[${blockId}][content][]" class="form-control" rows="2"></textarea>
                    <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3" onclick="this.parentElement.remove()" style="font-size: 14px;">&times;</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function tarikLaguBuilder(blockId, event) {
            const buku = document.getElementById(`buku-${blockId}`).value;
            const nomor = document.getElementById(`nomor-${blockId}`).value.trim();
            const judulInput = document.getElementById(`judul-${blockId}`);
            const container = document.getElementById(`bait-container-${blockId}`);
            const btn = event.currentTarget;
            
            if(!nomor) return alert('Tulis nomor lagu terlebih dahulu!');
            
            const originalText = btn.innerHTML;
            btn.innerHTML = '...'; btn.disabled = true;

            fetch(`/api/fetch-lagu?buku=${buku}&nomor=${nomor}`)
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        judulInput.value = data.judul;
                        container.innerHTML = ''; 
                        const baits = data.text.split('===SLIDE_BREAK===');
                        
                        baits.forEach((bait, idx) => {
                            let baitRaw = bait.trim();
                            if(baitRaw !== '') {
                                let isReff = baitRaw.toUpperCase().startsWith('[REFF]') || baitRaw.toLowerCase().startsWith('reff') || baitRaw.toLowerCase().startsWith('ref');
                                let cleanBait = baitRaw.replace(/^\[?REFF\]?\s*/i, '');
                                let labelText = isReff ? 'Reff' : 'Bait ' + (idx + 1);

                                const html = `
                                    <div class="input-group mb-2 position-relative">
                                        <span class="input-group-text ${isReff ? 'bg-warning text-dark' : 'bg-light text-secondary'} fw-bold" style="width: 75px; justify-content:center;">${labelText}</span>
                                        <textarea name="blocks[${blockId}][content][]" class="form-control" rows="3">${isReff ? '[REFF]\n' + cleanBait : cleanBait}</textarea>
                                        <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3" onclick="this.parentElement.remove()" style="font-size: 14px;">&times;</button>
                                    </div>
                                `;
                                container.insertAdjacentHTML('beforeend', html);
                            }
                        });
                    } else { alert(data.message); }
                })
                .catch(() => alert('Gagal menarik data lagu.'))
                .finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
        }

        function tarikAyatBuilder(blockId, event) {
            const input = document.getElementById(`cari-kitab-${blockId}`);
            const textarea = document.getElementById(`text-kitab-${blockId}`);
            const btn = event.currentTarget;
            const query = input.value.trim();
            
            if(!query) return alert('Tulis nama kitab dan pasalnya!');
            
            const originalText = btn.innerHTML;
            btn.innerHTML = '...'; btn.disabled = true;
            textarea.value = "Menghubungi server Alkitab...";

            fetch(`/api/fetch-alkitab?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        textarea.value = query.toUpperCase() + "\n===SLIDE_BREAK===\n" + data.text;
                    } else { textarea.value = "Gagal: " + data.message; }
                })
                .catch(() => textarea.value = "Gagal koneksi internet.")
                .finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
        }

        window.onload = function() {
            insertBlock('nyanyian', 'top');
        };
    </script>
</body>
</html>