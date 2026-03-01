<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Builder Presentasi - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; padding-bottom: 120px; color:#1e293b; }
        .navbar { background-color: #0f172a !important; }
        .builder-container { max-width: 800px; margin: auto; margin-top: 40px; }
        
        .block-card { background: white; border-radius: 8px; padding: 25px; margin-bottom: 20px; border: 1px solid #e2e8f0; border-left: 4px solid #3b82f6; position: relative; }
        .block-card:focus-within { box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1); }
        
        .toolbar-menu { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: #1e293b; padding: 10px 20px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); display: flex; gap: 8px; z-index: 1000; align-items: center; }
        .btn-add { background: #334155; color: #e2e8f0; border: none; font-size: 0.8rem; font-weight: 600; cursor: pointer; padding: 8px 15px; border-radius: 4px; transition: 0.2s; }
        .btn-add:hover { background: #475569; color: #fff; }
        .btn-save { background: #3b82f6; color: white; border: none; font-size: 0.85rem; font-weight: 700; padding: 8px 25px; border-radius: 4px; margin-left: 10px; transition: 0.2s;}
        .btn-save:hover { background: #2563eb; }
        
        .btn-delete-block { position: absolute; top: 15px; right: 15px; color: #cbd5e1; background: none; border: none; font-size: 1.2rem; cursor: pointer; }
        .btn-delete-block:hover { color: #ef4444; }
        
        input:focus, textarea:focus, select:focus { box-shadow: none !important; border-color: #3b82f6 !important; }
        .block-label { font-size: 0.75rem; letter-spacing: 1px; color: #64748b; margin-bottom: 10px; text-transform: uppercase; font-weight: 700;}
        textarea { resize: vertical; } 
    </style>
</head>
<body>

    <nav class="navbar navbar-dark shadow-sm py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand fw-bold m-0" href="{{ route('liturgy.gallery') }}">SISTEM MULTIMEDIA</a>
            <a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-light btn-sm fw-medium px-4">Batal & Kembali</a>
        </div>
    </nav>

    <div class="container builder-container">
        
        @if ($errors->any())
            <div class="alert alert-danger shadow-sm border-0 border-start border-4 border-danger mb-4">
                <strong>Gagal Menyimpan!</strong> Periksa kembali isian Anda:
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('liturgy.store_custom') }}" method="POST" id="builderForm">
            @csrf
            <div class="mb-4 border-bottom pb-4">
                <input type="text" name="schedule_name" class="form-control form-control-lg border-0 fw-bold fs-3 px-0 mb-3 bg-transparent" placeholder="Judul Jadwal (Cth: Ibadah Pemuda)" value="{{ old('schedule_name') }}" required>
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="small text-secondary fw-bold mb-1">Tanggal</label>
                        <input type="date" name="worship_date" class="form-control" value="{{ old('worship_date') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-secondary fw-bold mb-1">Tema / Sub Tema</label>
                        <input type="text" name="theme" class="form-control" placeholder="Opsional" value="{{ old('theme') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="small text-secondary fw-bold mb-1">Pelayan Firman</label>
                        <input type="text" name="preacher_name" class="form-control" placeholder="Nama Pelayan" value="{{ old('preacher_name') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="small text-secondary fw-bold mb-1">Warna Latar</label>
                        <input type="color" name="theme_color" class="form-control form-control-color w-100 p-1" value="{{ old('theme_color', '#1b2735') }}">
                    </div>
                </div>
            </div>

            <div id="canvas-area"></div>
        </form>
    </div>

    <div class="toolbar-menu">
        <span class="text-white small fw-medium me-2">Tambahkan:</span>
        <button type="button" class="btn-add" onclick="addBlock('nyanyian', 'Nyanyian Jemaat')">Lagu</button>
        <button type="button" class="btn-add" onclick="addBlock('alkitab', 'Pembacaan Alkitab')">Alkitab</button>
        <button type="button" class="btn-add" onclick="addBlock('polos', 'Votum dan Salam', 'Pertolongan kita adalah dalam nama Tuhan...')">Votum</button>
        <button type="button" class="btn-add" onclick="addBlock('polos', 'Pengakuan Iman Rasuli', 'Aku percaya kepada Allah Bapa yang Mahakuasa...')">Pengakuan Iman</button>
        <button type="button" class="btn-add" onclick="addBlock('aksi', 'Sikap Jemaat')">Sikap</button>
        <button type="button" class="btn-add" onclick="addBlock('polos', '', '')">Lainnya</button>
        <div style="width: 1px; height: 20px; background: #475569; margin: 0 5px;"></div>
        <button type="submit" form="builderForm" class="btn-save">SIMPAN & TAYANGKAN</button>
    </div>

<script>
    let blockCount = 0;

    function getNextVerseNumber(containerId) {
        const container = document.getElementById(containerId);
        let maxNum = 0;
        const textareas = container.querySelectorAll('textarea');
        textareas.forEach(ta => {
            if(ta.name.includes('[bait]')) {
                const match = ta.name.match(/\[bait\]\[(\d+)\]/);
                if(match && parseInt(match[1]) > maxNum) maxNum = parseInt(match[1]);
            }
        });
        return maxNum === 0 ? 1 : maxNum + 1;
    }

    function addBait(blockCount) {
        const containerId = 'bait-wrapper-' + blockCount;
        const nextNum = getNextVerseNumber(containerId);
        
        const html = `
            <div class="input-group mb-2 shadow-sm position-relative">
                <span class="input-group-text bg-light text-secondary" style="font-size:0.8rem; min-width: 70px;">Bait ${nextNum}</span>
                <textarea name="blocks[${blockCount}][bait][${nextNum}]" class="form-control bg-light" rows="4" placeholder="Bait lanjutan..."></textarea>
                <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.parentElement.remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
            </div>
        `;
        document.getElementById(containerId).insertAdjacentHTML('beforeend', html);
    }

    function addBlock(type, defaultTitle, defaultContent = '') {
        blockCount++;
        const canvas = document.getElementById('canvas-area');
        const blockId = 'block-' + blockCount;
        let htmlContent = '';

        if (type === 'nyanyian') {
            htmlContent = `
                <div class="block-label text-primary">Blok Nyanyian</div>
                <input type="hidden" name="blocks[${blockCount}][type]" value="nyanyian">
                <input type="text" id="judul-lagu-${blockCount}" name="blocks[${blockCount}][title]" class="form-control fw-bold fs-5 border-0 border-bottom mb-3 px-0 rounded-0" placeholder="Judul Slide" value="${defaultTitle}" required>
                
                <div class="input-group mb-3">
                    <select id="buku-lagu-${blockCount}" class="form-select bg-light" style="max-width: 100px;">
                        <option value="KJ">KJ</option><option value="NKB">NKB</option><option value="PKJ">PKJ</option><option value="NR">NR</option><option value="BEBAS">Lainnya</option>
                    </select>
                    <input type="text" id="nomor-lagu-${blockCount}" class="form-control bg-light" placeholder="No. Lagu (Cth: 15)">
                    <button type="button" class="btn btn-secondary fw-medium px-3" onclick="tarikLaguBuilder(${blockCount}, event)">Tarik Lirik</button>
                </div>
                
                <div id="bait-wrapper-${blockCount}">
                    <div class="input-group mb-2 shadow-sm position-relative">
                        <span class="input-group-text bg-light text-secondary" style="font-size:0.8rem; min-width: 70px;">Bait 1</span>
                        <textarea name="blocks[${blockCount}][bait][1]" class="form-control bg-light" rows="4" placeholder="Ketik lirik di sini..." required></textarea>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-light border w-100 mt-1 fw-medium text-secondary" onclick="addBait(${blockCount})">&plus; Tambah Bait Lanjutan Manual</button>
            `;
        } 
        else if (type === 'alkitab') {
            htmlContent = `
                <div class="block-label text-primary">Blok Bacaan Alkitab</div>
                <input type="hidden" name="blocks[${blockCount}][type]" value="alkitab">
                <input type="text" name="blocks[${blockCount}][title]" class="form-control fw-bold fs-5 border-0 border-bottom mb-3 px-0 rounded-0" placeholder="Judul Slide" value="${defaultTitle}" required>
                <div class="input-group mb-2">
                    <input type="text" id="cari-kitab-${blockCount}" class="form-control bg-light" placeholder="Cari ayat (Contoh: Yohanes 3:16)">
                    <button type="button" class="btn btn-secondary fw-medium" onclick="tarikAyat(${blockCount})">Tarik Teks</button>
                </div>
                <textarea id="text-kitab-${blockCount}" name="blocks[${blockCount}][content]" class="form-control" rows="6" placeholder="Teks akan ditarik ke sini otomatis..." required></textarea>
            `;
        } 
        else if (type === 'aksi') {
            htmlContent = `
                <div class="block-label text-primary">Instruksi Sikap Jemaat</div>
                <input type="hidden" name="blocks[${blockCount}][type]" value="aksi">
                <input type="hidden" name="blocks[${blockCount}][title]" value="Sikap Jemaat">
                <select name="blocks[${blockCount}][content]" class="form-select form-select-lg fw-bold bg-light text-dark">
                    <option value="(Jemaat Berdiri)">(Jemaat Berdiri)</option>
                    <option value="(Jemaat Duduk)">(Jemaat Duduk)</option>
                    <option value="(Saat Teduh)">(Saat Teduh / Lilin Dipadamkan)</option>
                </select>
            `;
        } 
        else {
            htmlContent = `
                <div class="block-label text-primary">Blok Teks Bebas</div>
                <input type="hidden" name="blocks[${blockCount}][type]" value="polos">
                <input type="text" name="blocks[${blockCount}][title]" class="form-control fw-bold fs-5 border-0 border-bottom mb-3 px-0 rounded-0" placeholder="Judul Slide" value="${defaultTitle}" required>
                <textarea name="blocks[${blockCount}][content]" class="form-control bg-light" rows="5" placeholder="Ketik teks di sini..." required>${defaultContent}</textarea>
            `;
        }

        const block = document.createElement('div');
        block.className = 'block-card';
        block.id = blockId;
        block.innerHTML = `<button type="button" class="btn-delete-block" onclick="document.getElementById('${blockId}').remove()">&times;</button>${htmlContent}`;
        canvas.appendChild(block);
        block.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
                    let verseNum = 1;
                    baits.forEach(bait => {
                        if(bait.trim() !== '') {
                            const html = `
                                <div class="input-group mb-2 shadow-sm position-relative">
                                    <span class="input-group-text bg-light text-secondary" style="font-size:0.8rem; min-width: 70px;">Bait ${verseNum}</span>
                                    <textarea name="blocks[${blockId}][bait][${verseNum}]" class="form-control bg-light" rows="4">${bait.trim()}</textarea>
                                    <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.parentElement.remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                                </div>
                            `;
                            container.insertAdjacentHTML('beforeend', html);
                            verseNum++;
                        }
                    });
                } else { alert(data.message); }
            })
            .catch(err => alert('Gagal menarik data lagu dari Database.'))
            .finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
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