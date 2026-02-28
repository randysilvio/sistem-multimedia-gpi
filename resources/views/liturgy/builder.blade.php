<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Builder Presentasi - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');
        body { background-color: #f4f6f8; font-family: 'Inter', sans-serif; padding-bottom: 120px; color:#2d3748; }
        .navbar { background-color: #1a202c !important; }
        .builder-container { max-width: 800px; margin: auto; margin-top: 40px; }
        
        .block-card { background: white; border-radius: 8px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border-left: 4px solid #cbd5e0; position: relative; transition: 0.2s; }
        .block-card:focus-within { border-left-color: #3182ce; box-shadow: 0 4px 12px rgba(49, 130, 206, 0.1); }
        
        .toolbar-menu { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: #1a202c; padding: 8px 15px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); display: flex; gap: 5px; z-index: 1000; align-items: center; }
        .btn-add { background: transparent; color: #a0aec0; border: none; font-size: 0.8rem; font-weight: 500; cursor: pointer; padding: 8px 15px; border-radius: 4px; transition: 0.2s; letter-spacing: 0.5px; }
        .btn-add:hover { background: #2d3748; color: #fff; }
        .btn-save { background: #2b6cb0; color: white; border: none; font-size: 0.8rem; font-weight: 600; padding: 8px 20px; border-radius: 4px; margin-left: 10px; transition: 0.2s;}
        .btn-save:hover { background: #3182ce; }
        
        .btn-delete-block { position: absolute; top: 15px; right: 15px; color: #a0aec0; background: none; border: none; font-size: 1.2rem; cursor: pointer; opacity: 0.6; transition: 0.2s;}
        .btn-delete-block:hover { color: #e53e3e; opacity: 1; }
        
        input:focus, textarea:focus, select:focus { box-shadow: none !important; border-color: #3182ce !important; }
        .block-label { font-size: 0.75rem; letter-spacing: 1px; color: #718096; margin-bottom: 10px; text-transform: uppercase; font-weight: 600;}
    </style>
</head>
<body>

    <nav class="navbar navbar-dark shadow-sm py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-uppercase m-0 d-flex align-items-center" href="{{ route('liturgy.gallery') }}">
                <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" alt="Logo GPI Papua" height="30" class="me-3">
                <span class="fw-bold" style="font-size: 1rem; letter-spacing: 1px;">Sistem Multimedia</span>
            </a>
            <a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-light btn-sm fw-medium px-4">Batal & Kembali</a>
        </div>
    </nav>

    <div class="container builder-container">
        <form action="{{ route('liturgy.store_custom') }}" method="POST" id="builderForm">
            @csrf
            <div class="block-card border-top border-4 border-primary mb-4" style="border-left: none; border-top-color: #2b6cb0 !important;">
                <input type="text" name="schedule_name" class="form-control form-control-lg border-0 fw-bold fs-3 px-0 mb-3" placeholder="Judul Presentasi Ibadah Kustom" required>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="small text-secondary fw-medium mb-1">Tanggal Ibadah</label>
                        <input type="date" name="worship_date" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-secondary fw-medium mb-1">Pelayan Firman</label>
                        <input type="text" name="preacher_name" class="form-control" placeholder="Nama Pelayan">
                    </div>
                    <div class="col-md-4">
                        <label class="small text-secondary fw-medium mb-1">Warna Tema Latar</label>
                        <input type="color" name="theme_color" class="form-control form-control-color w-100 p-1" value="#1b2735">
                    </div>
                </div>
            </div>

            <div id="canvas-area"></div>
            
            <div class="text-center mt-5">
                <p class="text-muted small">Gunakan toolbar melayang di bawah untuk menyusun urutan ibadah</p>
            </div>
        </form>
    </div>

    <div class="toolbar-menu">
        <button type="button" class="btn-add" onclick="addBlock('nyanyian')">Lagu &plus;</button>
        <button type="button" class="btn-add" onclick="addBlock('alkitab')">Alkitab &plus;</button>
        <button type="button" class="btn-add" onclick="addBlock('votum')">Votum &plus;</button>
        <button type="button" class="btn-add" onclick="addBlock('aksi')">Sikap &plus;</button>
        <button type="button" class="btn-add" onclick="addBlock('kosong')">Teks &plus;</button>
        <div style="width: 1px; height: 20px; background: #4a5568; margin: 0 5px;"></div>
        <button type="button" class="btn-save" onclick="document.getElementById('builderForm').submit()">SIMPAN</button>
    </div>

<script>
    let blockCount = 0;

    function addBlock(type) {
        blockCount++;
        const canvas = document.getElementById('canvas-area');
        const blockId = 'block-' + blockCount;
        let htmlContent = '';

        if (type === 'nyanyian') {
            htmlContent = `
                <div class="block-label text-primary">Blok Nyanyian</div>
                <input type="hidden" name="blocks[${blockCount}][type]" value="nyanyian">
                
                <div class="input-group mb-2">
                    <select id="buku-lagu-${blockCount}" class="form-select bg-light" style="max-width: 100px;">
                        <option value="KJ">KJ</option>
                        <option value="NKB">NKB</option>
                        <option value="PKJ">PKJ</option>
                        <option value="NR">NR</option>
                        <option value="BEBAS">Lainnya</option>
                    </select>
                    <input type="text" id="nomor-lagu-${blockCount}" class="form-control bg-light" placeholder="No. Lagu (Cth: 15)">
                    <button type="button" class="btn btn-secondary fw-medium px-3" onclick="tarikLaguBuilder(${blockCount}, event)">Tarik Lirik</button>
                </div>

                <input type="text" id="judul-lagu-${blockCount}" name="blocks[${blockCount}][title]" class="form-control fw-bold fs-5 border-0 border-bottom mb-3 px-0 rounded-0" placeholder="Judul Lagu (Contoh: Nyanyian Jemaat KJ 15)" required>
                
                <div id="bait-wrapper-${blockCount}">
                    <div class="input-group mb-2 shadow-sm position-relative">
                        <span class="input-group-text bg-light text-secondary" style="font-size:0.8rem;">Bait</span>
                        <textarea name="blocks[${blockCount}][content][]" class="form-control bg-light" rows="3" placeholder="Ketik bait pertama..." required></textarea>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-light border w-100 mt-1 fw-medium text-secondary" onclick="addBait(${blockCount})">&plus; Tambah Bait Lanjutan</button>
            `;
        } 
        else if (type === 'alkitab') {
            htmlContent = `
                <div class="block-label text-info">Blok Bacaan Alkitab</div>
                <input type="hidden" name="blocks[${blockCount}][type]" value="alkitab">
                <input type="text" name="blocks[${blockCount}][title]" class="form-control fw-bold fs-5 border-0 border-bottom mb-3 px-0 rounded-0" placeholder="Judul (Contoh: Pembacaan Firman)" required>
                <div class="input-group mb-2">
                    <input type="text" id="cari-kitab-${blockCount}" class="form-control bg-light" placeholder="Cari ayat (Contoh: Yohanes 3:16)">
                    <button type="button" class="btn btn-secondary fw-medium" onclick="tarikAyat(${blockCount})">Tarik Teks</button>
                </div>
                <textarea id="text-kitab-${blockCount}" name="blocks[${blockCount}][content]" class="form-control" rows="4" placeholder="Teks akan ditarik ke sini otomatis..." required></textarea>
            `;
        } 
        else if (type === 'aksi') {
            htmlContent = `
                <div class="block-label">Sikap / Instruksi Jemaat</div>
                <input type="hidden" name="blocks[${blockCount}][type]" value="aksi">
                <input type="hidden" name="blocks[${blockCount}][title]" value="Sikap Jemaat">
                <select name="blocks[${blockCount}][content]" class="form-select form-select-lg fw-medium bg-light">
                    <option value="(Jemaat Berdiri)">(Jemaat Berdiri)</option>
                    <option value="(Jemaat Duduk)">(Jemaat Duduk)</option>
                    <option value="(Saat Teduh)">(Saat Teduh / Lilin Dipadamkan)</option>
                    <option value="(Jemaat Duduk - Calon Sidi Berlutut)">(Jemaat Duduk - Calon Sidi Berlutut)</option>
                </select>
            `;
        } 
        else if (type === 'votum') {
            htmlContent = `
                <div class="block-label text-warning">Votum & Salam</div>
                <input type="hidden" name="blocks[${blockCount}][type]" value="votum">
                <input type="text" name="blocks[${blockCount}][title]" class="form-control fw-bold fs-5 border-0 border-bottom mb-3 px-0 rounded-0" value="Votum dan Salam">
                <textarea name="blocks[${blockCount}][content]" class="form-control bg-light" rows="3" placeholder="Ketik teks di sini..." required></textarea>
            `;
        } 
        else {
            htmlContent = `
                <div class="block-label text-success">Slide Teks Bebas</div>
                <input type="hidden" name="blocks[${blockCount}][type]" value="polos">
                <input type="text" name="blocks[${blockCount}][title]" class="form-control fw-bold fs-5 border-0 border-bottom mb-3 px-0 rounded-0" placeholder="Judul Slide (Contoh: Pengumuman)">
                <textarea name="blocks[${blockCount}][content]" class="form-control bg-light" rows="3" placeholder="Ketik teks bebas..."></textarea>
            `;
        }

        const block = document.createElement('div');
        block.className = 'block-card';
        block.id = blockId;
        block.innerHTML = `<button type="button" class="btn-delete-block" onclick="document.getElementById('${blockId}').remove()">&times;</button>${htmlContent}`;
        canvas.appendChild(block);
        block.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const firstInput = block.querySelector('input[type="text"]');
        if(firstInput) firstInput.focus();
    }

    function addBait(blockId) {
        const wrapper = document.getElementById(`bait-wrapper-${blockId}`);
        const html = `
            <div class="input-group mb-2 shadow-sm position-relative">
                <span class="input-group-text bg-light text-secondary" style="font-size:0.8rem;">Bait</span>
                <textarea name="blocks[${blockId}][content][]" class="form-control bg-light" rows="3" placeholder="Bait lanjutan..."></textarea>
                <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.parentElement.remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
            </div>
        `;
        wrapper.insertAdjacentHTML('beforeend', html);
    }

    function tarikLaguBuilder(blockId, event) {
        const buku = document.getElementById(`buku-lagu-${blockId}`).value;
        const nomor = document.getElementById(`nomor-lagu-${blockId}`).value.trim();
        const judulInput = document.getElementById(`judul-lagu-${blockId}`);
        const container = document.getElementById(`bait-wrapper-${blockId}`);
        const btn = event.currentTarget;
        
        if(!nomor) return alert('Masukkan nomor lagu terlebih dahulu!');
        
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Memuat...'; btn.disabled = true;

        fetch(`/api/fetch-lagu?buku=${buku}&nomor=${nomor}`)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    judulInput.value = buku + " " + nomor + " - " + data.judul;
                    container.innerHTML = ''; 
                    
                    const baits = data.text.split('===SLIDE_BREAK===');
                    baits.forEach(bait => {
                        if(bait.trim() !== '') {
                            const html = `
                                <div class="input-group mb-2 shadow-sm position-relative">
                                    <span class="input-group-text bg-light text-secondary" style="font-size:0.8rem;">Bait</span>
                                    <textarea name="blocks[${blockId}][content][]" class="form-control bg-light" rows="3">${bait.trim()}</textarea>
                                    <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.parentElement.remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                                </div>
                            `;
                            container.insertAdjacentHTML('beforeend', html);
                        }
                    });
                } else { 
                    alert(data.message); 
                }
            })
            .catch(err => alert('Gagal menarik data lagu dari Database.'))
            .finally(() => { 
                btn.innerHTML = originalText; 
                btn.disabled = false; 
            });
    }

    function tarikAyat(blockId) {
        const input = document.getElementById(`cari-kitab-${blockId}`);
        const textarea = document.getElementById(`text-kitab-${blockId}`);
        const query = input.value.trim();
        if(!query) return alert('Tulis nama kitab dan pasalnya terlebih dahulu.');
        
        textarea.value = "Memuat data...";
        fetch(`/api/fetch-alkitab?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    textarea.value = query.toUpperCase() + "\n===SLIDE_BREAK===\n" + data.text;
                } else {
                    textarea.value = "Gagal: " + data.message;
                }
            })
            .catch(() => textarea.value = "Gagal koneksi internet.");
    }
</script>
</body>
</html>