@extends('layouts.admin')

@section('eyebrow', 'Jadwal & Presentasi')
@section('title')
Presentasi Kustom
@endsection

@section('actions')
<a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-secondary">Batal</a>
@endsection

@section('content')


    <div class="container builder-container">
        <form action="{{ route('liturgy.store_custom') }}" method="POST" id="mainForm">
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
            </div>
            
            <div id="canvas-area">
                <div class="insert-divider top-divider" id="top-anchor">
                    <div class="dropdown">
                        <button type="button" class="btn-insert" data-bs-toggle="dropdown" aria-expanded="false" title="Tambah Slide Paling Atas">&plus;</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('nyanyian', 'top')">Tambah Nyanyian Jemaat</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('alkitab', 'top')">Tambah Bacaan Alkitab</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('polos', 'top')">Tambah Slide Teks Bebas / Sikap</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="toolbar-menu">
                <div class="container d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary btn-primary-custom">Simpan & rakit presentasi</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
        let blockCount = 0;

        function insertBlock(type, referenceId) {
            blockCount++;
            const newId = blockCount;
            let cardColor = '';
            let typeLabel = '';
            let contentHtml = '';

            // Fitur Kamera HTML
            const cameraToggleHtml = `
                <div class="form-check form-switch mt-3 pt-2 border-top">
                    <input class="form-check-input" type="checkbox" name="blocks[${newId}][use_camera]" value="1" id="cam-${newId}">
                    <label class="form-check-label small fw-bold text-muted" for="cam-${newId}">
                        Sorot pengisi acara (latar kamera, teks di bawah)
                    </label>
                </div>
            `;

            if (type === 'nyanyian') {
                cardColor = '#2b6cb0'; typeLabel = 'NYANYIAN JEMAAT';
                contentHtml = `
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-muted">Keterangan / Momen</label>
                        <input type="text" name="blocks[${newId}][title]" class="form-control fw-bold" placeholder="Cth: Nyanyian Persiapan" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small fw-bold text-muted">Tarik Data Lagu</label>
                        <div class="input-group mb-2 song-ref">
                            <select id="buku-${newId}" class="form-select bg-light" style="max-width: 90px;">
                                <option value="KJ">KJ</option><option value="NKB">NKB</option><option value="PKJ">PKJ</option><option value="NR">NR</option><option value="DSL">DSL</option><option value="GPM">GPM</option><option value="BEBAS">Lainnya</option>
                            </select>
                            <input type="text" id="nomor-${newId}" class="form-control" placeholder="No. Lagu">
                            <button type="button" class="btn btn-outline-primary" data-song-picker title="Pilih dari lagu yang sudah ada di database">Pilih</button>
                            <button type="button" class="btn btn-secondary px-3" data-song-pull onclick="tarikLaguBuilder(${newId}, event)">Tarik</button>
                        </div>
                        <input type="text" id="judul-${newId}" name="blocks[${newId}][judul]" class="form-control mb-2 text-primary fw-medium" placeholder="Judul otomatis terdeteksi..." style="font-size: 0.85rem;">
                        
                        <div id="bait-container-${newId}" class="mt-3">
                            <div class="input-group mb-2 position-relative bait-item shadow-sm">
                                <div class="input-group-text bg-light text-secondary p-0 overflow-hidden" style="width: 80px;">
                                    <input type="text" class="bait-label-input" onchange="updateBaitName(this, '${newId}')" value="1" placeholder="Angka">
                                </div>
                                <input type="hidden" class="bait-hidden-key" name="blocks[${newId}][bait][1]" value="">
                                <textarea class="form-control" rows="2" placeholder="Isi lirik..." oninput="this.previousElementSibling.value = this.value"></textarea>
                                <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.closest('.bait-item').remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" style="font-size:0.75rem; font-weight:600;" onclick="tambahBaitLagu(${newId})">+ Tambah Bait Manual</button>
                    </div>
                ` + cameraToggleHtml;
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
                            <input type="text" id="cari-kitab-${newId}" name="blocks[${newId}][ref]" class="form-control" placeholder="Cth: Yoh 3:16-18 · Mzm 23" onkeydown="if(event.key==='Enter'){event.preventDefault();this.nextElementSibling.click();}">
                            <button type="button" class="btn btn-secondary px-3" onclick="tarikAyatBuilder(${newId}, event)">Tarik</button>
                        </div>
                        <textarea name="blocks[${newId}][content][]" id="text-kitab-${newId}" class="form-control bg-light" rows="4" placeholder="Teks ayat..."></textarea>
                    </div>
                ` + cameraToggleHtml;
            }
            else {
                cardColor = '#718096'; typeLabel = 'SLIDE TEKS BEBAS';
                contentHtml = `
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-muted">Judul Slide (Opsional)</label>
                        <input type="text" name="blocks[${newId}][title]" class="form-control fw-bold" placeholder="Kosongkan jika ingin teks di tengah layar (Instruksi)">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small fw-bold text-muted">Isi Konten Teks</label>
                        <textarea name="blocks[${newId}][content][]" class="form-control" rows="3" placeholder="Ketik pengumuman, votum, atau sikap jemaat di sini..."></textarea>
                    </div>
                ` + cameraToggleHtml;
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
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('polos', '${newId}')">Tambah Slide Teks Bebas / Sikap</a></li>
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

        // PERBAIKAN: Fungsi Update Nama Bait dengan Unique Key (Mencegah Reff tertimpa)
        function updateBaitName(inputElement, blockId) {
            let newName = inputElement.value.trim();
            if(newName === '') newName = 'Bait';
            
            let parentDiv = inputElement.closest('.input-group-text');
            
            if(newName.toLowerCase() === 'reff') {
                parentDiv.classList.replace('bg-light', 'bg-warning');
                parentDiv.classList.replace('text-secondary', 'text-dark');
            } else {
                parentDiv.classList.replace('bg-warning', 'bg-light');
                parentDiv.classList.replace('text-dark', 'text-secondary');
            }
            
            let hiddenInput = inputElement.closest('.bait-item').querySelector('.bait-hidden-key');
            if(hiddenInput) {
                let formKey = newName;
                if(newName.toLowerCase() === 'reff') {
                    let existingKey = hiddenInput.name.match(/\[bait\]\[(.*?)\]/);
                    if (existingKey && existingKey[1].toLowerCase().includes('ref')) {
                        formKey = existingKey[1]; 
                    } else {
                        formKey = 'ref_' + Math.random().toString(36).substr(2, 5); 
                    }
                }
                hiddenInput.name = `blocks[${blockId}][bait][${formKey}]`;
            }
        }

        function tambahBaitLagu(blockId) {
            const container = document.getElementById(`bait-container-${blockId}`);
            const baitNum = Date.now().toString().slice(-4); 
            
            const html = `
                <div class="input-group mb-2 position-relative bait-item shadow-sm">
                    <div class="input-group-text bg-light text-secondary p-0 overflow-hidden" style="width: 80px;">
                        <input type="text" class="bait-label-input" onchange="updateBaitName(this, '${blockId}')" value="${baitNum}" placeholder="Angka">
                    </div>
                    <input type="hidden" class="bait-hidden-key" name="blocks[${blockId}][bait][${baitNum}]" value="">
                    <textarea class="form-control" rows="3" oninput="this.previousElementSibling.value = this.value"></textarea>
                    <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.closest('.bait-item').remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
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
                        
                        // PERBAIKAN: Penomoran bait tidak menghitung bagian Reff
                        let verseCounter = 1;

                        baits.forEach((bait, idx) => {
                            let baitRaw = bait.trim();
                            if(baitRaw !== '') {
                                let isReff = baitRaw.toUpperCase().startsWith('[REFF]') || baitRaw.toLowerCase().startsWith('reff') || baitRaw.toLowerCase().startsWith('ref');
                                let cleanBait = baitRaw.replace(/^\[?REFF\]?\s*/i, '');
                                
                                let labelText = isReff ? 'Reff' : ((data.labels && data.labels[idx] && data.labels[idx] !== 'Reff') ? data.labels[idx] : verseCounter);
                                let bgClass = isReff ? 'bg-warning text-dark' : 'bg-light text-secondary';
                                let formKey = isReff ? 'ref_' + idx : labelText;

                                const html = `
                                    <div class="input-group mb-2 position-relative bait-item shadow-sm">
                                        <div class="input-group-text ${bgClass} p-0 overflow-hidden" style="width: 80px;">
                                            <input type="text" class="bait-label-input" onchange="updateBaitName(this, '${blockId}')" value="${labelText}">
                                        </div>
                                        <input type="hidden" class="bait-hidden-key" name="blocks[${blockId}][bait][${formKey}]" value="${escHtml(isReff ? '[REFF]\n' + cleanBait : cleanBait)}">
                                        <textarea class="form-control" rows="3" oninput="this.previousElementSibling.value = this.value">${escHtml(isReff ? '[REFF]\n' + cleanBait : cleanBait)}</textarea>
                                        <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.closest('.bait-item').remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                                    </div>
                                `;
                                container.insertAdjacentHTML('beforeend', html);
                                
                                if(!isReff) verseCounter++; // Hanya tambah nomor jika bukan Reff
                            }
                        });
                    } else { alert(data.message); }
                })
                .catch(() => alert('Gagal menarik data lagu.'))
                .finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
        }

        // Lirik dimasukkan ke HTML: tanda kutip/< > harus di-escape agar lirik tidak terpotong
        function escHtml(s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }

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
                        input.value = data.reference;
                        textarea.value = data.text;
                    } else { textarea.value = "Gagal: " + data.message; }
                })
                .catch(() => textarea.value = "Gagal koneksi internet.")
                .finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
        }

        window.onload = function() {
            insertBlock('nyanyian', 'top');
        };
    </script>
@endpush
