<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Jadwal Ibadah - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; color: #1e293b; padding-bottom: 120px; }
        .navbar { background-color: #0f172a !important; }
        .builder-container { max-width: 800px; margin: auto; margin-top: 30px; }
        
        .block-card { background: white; border-radius: 8px; padding: 25px; margin-bottom: 20px; border: 1px solid #e2e8f0; position: relative; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: 0.2s; }
        .block-card:focus-within { box-shadow: 0 4px 15px rgba(43, 108, 176, 0.15); border-color: #90cdf4; }
        
        .btn-delete-block { position: absolute; top: 15px; right: 15px; background: transparent; border: 1px solid #e2e8f0; color: #ef4444; font-size: 1.2rem; cursor: pointer; width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; transition: 0.2s; line-height: 1; z-index: 10;}
        .btn-delete-block:hover { background: #fee2e2; border-color: #fca5a5; transform: scale(1.05); }

        /* SOLID BOTTOM TOOLBAR PROFESIONAL */
        .toolbar-menu { position: fixed; bottom: 0; left: 0; width: 100%; background: #ffffff; padding: 15px 0; border-top: 1px solid #e2e8f0; box-shadow: 0 -4px 20px rgba(0,0,0,0.04); z-index: 900; }
        
        .btn-primary-custom { background-color: #0f172a; color: white; border: none; font-weight: 600; letter-spacing: 0.5px; padding: 10px 30px; border-radius: 6px; transition: 0.2s;}
        .btn-primary-custom:hover { background-color: #1e293b; color: white; }

        /* Input khusus modifikasi nama bait */
        .bait-label-input { width: 100%; border: none; font-size: 0.8rem; font-weight: 700; text-align: center; background: transparent; color: inherit; }
        .bait-label-input:focus { outline: none; background: rgba(0,0,0,0.05); }
    </style>
</head>
<body>

    @php
    if (!function_exists('cleanSlideTitle')) {
        function cleanSlideTitle($title) {
            if (!is_string($title)) return '';
            $title = str_ireplace('(opsional)', '', $title);
            $title = preg_replace('/slide bebas\s*[:\-]?\s*/i', '', $title);
            $title = preg_replace('/^nyanyian\s*[:\-]\s*/i', '', $title);
            $title = preg_replace('/^nyanyian\s*$/i', '', trim($title));
            return trim($title, " -:");
        }
    }
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm py-3 mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-uppercase m-0 d-flex align-items-center" href="{{ route('liturgy.gallery') }}">
                <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" alt="Logo GPI" height="32" class="me-3">
                <span>Pengisian Jadwal Ibadah</span>
            </a>
            <a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-light btn-sm fw-medium px-4">Batal & Kembali</a>
        </div>
    </nav>

    <div class="container builder-container">
        
        <div class="card border-0 shadow-sm mb-4 rounded-3">
            <div class="card-body p-4 bg-light">
                <form action="{{ route('liturgy.create') }}" method="GET" class="d-flex align-items-center gap-3">
                    <label class="form-label fw-bold text-dark m-0" style="white-space: nowrap;">Ganti Template:</label>
                    <select name="liturgy_id" class="form-select border-secondary fw-bold" onchange="this.form.submit()">
                        @foreach($liturgies as $l)
                            <option value="{{ $l->id }}" {{ $liturgy->id == $l->id ? 'selected' : '' }}>
                                {{ strtoupper($l->name) }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm border-0 border-start border-4 border-danger mb-4">
                <strong>Gagal Menyimpan!</strong> Periksa isian Anda.
            </div>
        @endif

        <form action="{{ route('liturgy.store') }}" method="POST" id="createForm">
            @csrf
            <input type="hidden" name="liturgy_id" value="{{ $liturgy->id }}">

            <div class="card border-0 shadow-sm mb-4 rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3 text-uppercase" style="letter-spacing: 1px; font-size:0.85rem;">Informasi Dasar Ibadah</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Tanggal Ibadah</label>
                            <input type="date" name="worship_date" class="form-control bg-light" value="{{ old('worship_date') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Tema / Sub Tema (Opsional)</label>
                            <input type="text" name="theme" class="form-control bg-light" placeholder="Cth: Ibadah Raya" value="{{ old('theme') }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-secondary">Pelayan Firman</label>
                            <input type="text" name="preacher_name" class="form-control bg-light" placeholder="Nama Pelayan" value="{{ old('preacher_name') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Warna Tema Utama</label>
                            <input type="color" name="theme_color" class="form-control form-control-color w-100 bg-light" value="{{ old('theme_color', '#1b2735') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-end mt-5 mb-3">
                <h6 class="fw-bold text-dark text-uppercase m-0" style="letter-spacing: 1px; font-size:0.85rem;">Pengisian Teks & Lirik Slide</h6>
                <small class="text-muted">Berdasarkan Template: {{ $liturgy->name }}</small>
            </div>

            @foreach($liturgy->items as $item)
                @php 
                    $isOptional = str_contains(strtolower($item->title), 'opsional'); 
                    $reqRule = $isOptional ? '' : 'required';
                    $val = $item->static_content ?? '';
                    
                    // PENYEDERHANAAN LOGIKA: 3 JENIS KONTEN SAJA
                    $titleLower = strtolower($item->title);
                    $cleanTitle = cleanSlideTitle($item->title);

                    $type = 'teks_bebas';
                    $cardColor = '#718096'; 
                    $typeLabel = 'TEKS BEBAS / INSTRUKSI'; 

                    if (str_contains($titleLower, 'nyanyian') || str_contains($titleLower, 'pujian')) { 
                        $type = 'nyanyian'; 
                        $cardColor = '#2b6cb0'; 
                        $typeLabel = 'NYANYIAN JEMAAT'; 
                    } elseif (str_contains($titleLower, 'alkitab') || str_contains($titleLower, 'bacaan')) { 
                        $type = 'alkitab'; 
                        $cardColor = '#2c5282'; 
                        $typeLabel = 'BACAAN ALKITAB'; 
                    }
                @endphp

                <div class="block-card" style="border-top: 4px solid {{ $cardColor }};">
                    <div class="mb-3 pb-2 d-flex justify-content-between align-items-center">
                        <span class="badge text-uppercase" style="background-color: {{ $cardColor }}; letter-spacing: 0.5px;">{{ $typeLabel }}</span>
                        <span class="fw-bold text-dark">{{ $item->title }}</span>
                    </div>

                    @if($item->is_dynamic)
                        
                        @if($type === 'nyanyian')
                            <div class="row g-2 mb-2">
                                <div class="col-md-3">
                                    <select id="buku-lagu-{{ $item->id }}" class="form-select bg-light">
                                        <option value="KJ">KJ</option><option value="NKB">NKB</option><option value="PKJ">PKJ</option><option value="NR">NR</option><option value="BEBAS">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <input type="text" id="nomor-lagu-{{ $item->id }}" class="form-control bg-light" placeholder="No. Lagu">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-secondary w-100" onclick="tarikLagu({{ $item->id }}, event)">Tarik</button>
                                </div>
                            </div>

                            <input type="text" name="dynamic_content[{{ $item->id }}][judul]" class="form-control mb-3 fw-bold text-primary" placeholder="Judul Lagu">
                            
                            <div id="bait-container-{{ $item->id }}">
                                <div class="input-group mb-2 shadow-sm position-relative bait-item">
                                    <div class="input-group-text bg-light text-secondary p-0 overflow-hidden" style="width: 80px;">
                                        <input type="text" class="bait-label-input" onchange="updateBaitName(this, '{{ $item->id }}')" value="1">
                                    </div>
                                    <input type="hidden" class="bait-hidden-key" name="dynamic_content[{{ $item->id }}][bait][1]" value="">
                                    <textarea class="form-control" rows="3" placeholder="Ketik lirik..." oninput="this.previousElementSibling.value = this.value"></textarea>
                                    <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.closest('.bait-item').remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="tambahBait({{ $item->id }})">&plus; Tambah Bait Lirik</button>
                            
                        @elseif($type === 'alkitab')
                            <div class="input-group mb-2">
                                <input type="text" id="input-alkitab-{{ $item->id }}" class="form-control bg-light" placeholder="Cari Kitab (Cth: Yohanes 3:16)">
                                <button type="button" class="btn btn-secondary px-4" onclick="tarikAlkitab({{ $item->id }}, event)">Tarik</button>
                            </div>
                            <textarea id="textarea-{{ $item->id }}" name="dynamic_content[{{ $item->id }}][content]" class="form-control" rows="4" placeholder="Teks ayat..." {{ $reqRule }}>{{ old('dynamic_content.'.$item->id.'.content', $val) }}</textarea>

                        @else
                            {{-- LOGIKA PINTAR TEKS BEBAS: Jika judul dihapus, otomatis tulisan akan ke tengah dan membesar di proyektor --}}
                            <label class="form-label small fw-bold text-secondary">Judul Sesi (Opsional)</label>
                            <input type="text" name="dynamic_content[{{ $item->id }}][custom_title]" class="form-control mb-2 fw-bold text-dark" placeholder="Kosongkan jika ingin teks di tengah layar (Utk Sikap Jemaat)" value="{{ $cleanTitle }}">
                            
                            <label class="form-label small fw-bold text-secondary">Isi Teks</label>
                            <textarea id="textarea-{{ $item->id }}" name="dynamic_content[{{ $item->id }}][content]" class="form-control" rows="3" placeholder="Ketik pengumuman, votum, atau sikap jemaat di sini..." {{ $reqRule }}>{{ old('dynamic_content.'.$item->id.'.content', $val) }}</textarea>
                        @endif
                        
                        <div class="form-check form-switch mt-3 pt-3 border-top">
                            <input class="form-check-input" type="checkbox" name="dynamic_content[{{ $item->id }}][use_camera]" value="true" id="cam-{{ $item->id }}">
                            <label class="form-check-label small fw-bold text-muted" for="cam-{{ $item->id }}">
                                📽️ Sorot Pengisi Acara (Aktifkan Latar Kamera & Teks Bawah)
                            </label>
                        </div>

                    @else
                        <textarea name="dynamic_content[{{ $item->id }}]" class="form-control text-secondary bg-light" rows="2" readonly>{{ $val }}</textarea>
                    @endif

                    <div class="mt-4 pt-3 border-top" style="border-color: #edf2f7 !important;">
                        <button type="button" class="btn btn-sm btn-light border fw-medium px-4 rounded" onclick="tambahSlideKhusus({{ $item->id }})">
                            &plus; Sisipkan Slide Tambahan (Kustom)
                        </button>
                        <div id="custom-slide-container-{{ $item->id }}" class="mt-2"></div>
                    </div>
                </div>
            @endforeach

            <div class="toolbar-menu">
                <div class="container d-flex justify-content-end">
                    <button type="submit" form="createForm" class="btn btn-primary-custom">SIMPAN JADWAL IBADAH</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function updateBaitName(inputElement, itemId) {
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
                hiddenInput.name = `dynamic_content[${itemId}][bait][${newName}]`;
            }
        }

        function tambahBait(itemId) {
            const containerId = 'bait-container-' + itemId;
            const baitNum = Date.now().toString().slice(-4);
            
            const html = `
                <div class="input-group mb-2 shadow-sm position-relative bait-item">
                    <div class="input-group-text bg-light text-secondary p-0 overflow-hidden" style="width: 80px;">
                        <input type="text" class="bait-label-input" onchange="updateBaitName(this, '${itemId}')" value="${baitNum}" placeholder="Angka">
                    </div>
                    <input type="hidden" class="bait-hidden-key" name="dynamic_content[${itemId}][bait][${baitNum}]" value="">
                    <textarea class="form-control" rows="3" oninput="this.previousElementSibling.value = this.value"></textarea>
                    <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.closest('.bait-item').remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                </div>`;
            document.getElementById(containerId).insertAdjacentHTML('beforeend', html);
        }

        function tambahSlideKhusus(itemId) {
            const container = document.getElementById('custom-slide-container-' + itemId);
            const slideId = Math.random().toString(36).substr(2, 9);
            const html = `
                <div class="p-3 mb-2 border rounded bg-light position-relative">
                    <button type="button" class="btn-delete-block" onclick="this.parentElement.remove()" style="top:5px; right:5px;">&times;</button>
                    <div class="mb-2 pe-4">
                        <input type="text" name="custom_slides[${itemId}][${slideId}][title]" class="form-control fw-medium" placeholder="Judul Slide Sisipan (Kosongkan jika teks di tengah)" >
                    </div>
                    <textarea name="custom_slides[${itemId}][${slideId}][content]" class="form-control" rows="3" placeholder="Isi konten slide..." required></textarea>
                    
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="custom_slides[${itemId}][${slideId}][use_camera]" value="true" id="cam-custom-${slideId}">
                        <label class="form-check-label small fw-bold text-muted" for="cam-custom-${slideId}">📽️ Latar Kamera (Lower Third)</label>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function tarikLagu(itemId, event) {
            const buku = document.getElementById(`buku-lagu-${itemId}`).value;
            const nomor = document.getElementById(`nomor-lagu-${itemId}`).value.trim();
            const judulInput = document.querySelector(`input[name="dynamic_content[${itemId}][judul]"]`);
            const container = document.getElementById(`bait-container-${itemId}`);
            const btn = event.currentTarget;
            
            if(!nomor) return alert('Masukkan nomor lagu!');
            
            const originalText = btn.innerHTML;
            btn.innerHTML = '...'; btn.disabled = true;

            fetch(`/api/fetch-lagu?buku=${buku}&nomor=${nomor}`)
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        judulInput.value = data.judul;
                        container.innerHTML = ''; 
                        
                        const baits = data.text.split('===SLIDE_BREAK===');
                        let verseCounter = 1;

                        baits.forEach((bait, idx) => {
                            let baitRaw = bait.trim();
                            if(baitRaw !== '') {
                                let isReff = baitRaw.toUpperCase().startsWith('[REFF]') || baitRaw.toLowerCase().startsWith('reff') || baitRaw.toLowerCase().startsWith('ref');
                                let cleanBait = baitRaw.replace(/^\[?REFF\]?\s*/i, '');
                                
                                let labelText = isReff ? 'Reff' : verseCounter;
                                let bgClass = isReff ? 'bg-warning text-dark' : 'bg-light text-secondary';
                                
                                const html = `
                                    <div class="input-group mb-2 shadow-sm position-relative bait-item">
                                        <div class="input-group-text ${bgClass} p-0 overflow-hidden" style="width: 80px;">
                                            <input type="text" class="bait-label-input" onchange="updateBaitName(this, '${itemId}')" value="${labelText}">
                                        </div>
                                        <input type="hidden" class="bait-hidden-key" name="dynamic_content[${itemId}][bait][${labelText}]" value="${isReff ? '[REFF]\\n' + cleanBait : cleanBait}">
                                        <textarea class="form-control" rows="3" oninput="this.previousElementSibling.value = this.value">${isReff ? '[REFF]\\n' + cleanBait : cleanBait}</textarea>
                                        <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.closest('.bait-item').remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                                    </div>`;
                                container.insertAdjacentHTML('beforeend', html);
                                
                                if(!isReff) verseCounter++;
                            }
                        });
                    } else { alert(data.message); }
                }).catch(err => alert('Gagal menarik data lagu.')).finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
        }

        function tarikAlkitab(itemId, event) {
            const inputField = document.getElementById('input-alkitab-' + itemId);
            const textarea = document.getElementById('textarea-' + itemId);
            const btn = event.currentTarget;
            const query = inputField.value.trim();
            
            if(!query) { alert('Tulis nama kitab dan pasalnya.'); return; }
            
            const originalText = btn.innerHTML;
            btn.innerHTML = '...'; btn.disabled = true;
            textarea.value = "Memuat data...";
            
            fetch(`/api/fetch-alkitab?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        textarea.value = query.toUpperCase() + "\n===SLIDE_BREAK===\n" + data.text;
                    } else { alert(data.message); }
                })
                .catch(err => alert('Terjadi kesalahan koneksi.'))
                .finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
        }
    </script>
</body>
</html>