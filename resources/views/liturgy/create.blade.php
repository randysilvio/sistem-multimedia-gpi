<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Jadwal Ibadah - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');
        body { background-color: #f4f6f8; font-family: 'Inter', sans-serif; color: #2d3748; }
        .navbar { background-color: #1a202c !important; }
        .form-control:focus, .form-select:focus { border-color: #3182ce; box-shadow: 0 0 0 0.2rem rgba(49, 130, 206, 0.15); }
        .card-edit { background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);}
        .form-label-header { font-weight: 600; color: #4a5568; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; margin-bottom: 12px; display: block; border-bottom: 1px solid #edf2f7; padding-bottom: 8px; }
        .btn-primary-custom { background-color: #2b6cb0; color: white; border: none; }
        .btn-primary-custom:hover { background-color: #2c5282; color: white; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4 py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-uppercase m-0 d-flex align-items-center" href="{{ route('liturgy.gallery') }}">
                <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" alt="Logo GPI Papua" height="30" class="me-3">
                <span class="fw-bold" style="font-size: 1rem; letter-spacing: 1px;">Sistem Multimedia</span>
            </a>
            <div>
                <a href="{{ route('liturgy.create') }}" class="btn btn-light fw-medium px-4 text-primary me-2">Buat Jadwal Baru</a>
                <a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-light btn-sm fw-medium px-4">Batal & Kembali</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-10">

                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body bg-white p-4">
                        <form action="{{ route('liturgy.create') }}" method="GET">
                            <label class="form-label fw-bold text-dark fs-6 text-uppercase" style="letter-spacing: 0.5px;">Pilih Tata Ibadah</label>
                            <select name="liturgy_id" class="form-select form-select-lg border-secondary bg-light" onchange="this.form.submit()">
                                @foreach($liturgies as $l)
                                    <option value="{{ $l->id }}" {{ $liturgy->id == $l->id ? 'selected' : '' }}>
                                        {{ strtoupper($l->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white p-4 border-bottom">
                        <h5 class="mb-0 fw-bold text-primary text-uppercase" style="letter-spacing: 0.5px;">Form Input: {{ $liturgy->name }}</h5>
                    </div>
                    <div class="card-body bg-light p-4">

                        <form action="{{ route('liturgy.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="liturgy_id" value="{{ $liturgy->id }}">

                            <div class="row mb-4 bg-white p-4 rounded-3 border" style="border-color: #e2e8f0 !important;">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold text-secondary small text-uppercase">Tanggal Ibadah</label>
                                    <input type="date" name="worship_date" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold text-secondary small text-uppercase">Tema / Sub Tema</label>
                                    <input type="text" name="theme" class="form-control" placeholder="Opsional">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold text-secondary small text-uppercase">Pelayan Firman</label>
                                    <input type="text" name="preacher_name" class="form-control" placeholder="Nama Pelayan">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold text-secondary small text-uppercase">Warna Liturgis</label>
                                    <input type="color" name="theme_color" class="form-control form-control-color w-100" value="{{ $liturgy->default_color ?? '#1b2735' }}" title="Warna dasar tema ibadah">
                                </div>
                            </div>

                            @foreach($liturgy->items as $item)
                                @php 
                                    $isOptional = str_contains(strtolower($item->title), 'opsional'); 
                                    $reqRule = $isOptional ? '' : 'required';
                                    $val = $item->static_content ?? '';
                                @endphp

                                <div class="card-edit bg-white position-relative">
                                    <label class="form-label-header d-flex justify-content-between align-items-center">
                                        <span>{{ $item->title }}</span>
                                        @if($isOptional) <span class="badge bg-light text-secondary border fw-normal" style="text-transform: none;">Opsional</span> @endif
                                    </label>
                                    
                                    @if($item->is_dynamic)
                                        
                                        @if(str_contains(strtolower($item->title), 'pra-ibadah') || str_contains(strtolower($item->title), 'prosesi'))
                                            <input type="text" name="dynamic_content[{{ $item->id }}][custom_title]" class="form-control mb-2 fw-medium text-dark" placeholder="Judul Prosesi (Opsional)" value="{{ str_replace(' (Opsional)', '', $item->title) }}">
                                            <textarea name="dynamic_content[{{ $item->id }}][content]" class="form-control" rows="4" placeholder="Ketik teks di sini..." {{ $reqRule }}>{{ $val }}</textarea>
                                            
                                        @elseif(str_contains(strtolower($item->title), 'nyanyian') || str_contains(strtolower($item->title), 'pujian'))
                                            <div class="input-group mb-2">
                                                <select id="buku-lagu-{{ $item->id }}" class="form-select bg-light" style="max-width: 100px;">
                                                    <option value="KJ">KJ</option>
                                                    <option value="NKB">NKB</option>
                                                    <option value="PKJ">PKJ</option>
                                                    <option value="NR">NR</option>
                                                    <option value="BEBAS">Lainnya</option>
                                                </select>
                                                <input type="text" id="nomor-lagu-{{ $item->id }}" class="form-control bg-light" placeholder="No. Lagu (Cth: 15)">
                                                <button type="button" class="btn btn-secondary fw-medium px-3" onclick="tarikLagu({{ $item->id }}, event)">Tarik Lirik</button>
                                            </div>
                                            <input type="text" name="dynamic_content[{{ $item->id }}][judul]" class="form-control mb-2 fw-medium text-primary" placeholder="Judul Lagu Manual / Otomatis">
                                            <div id="bait-container-{{ $item->id }}">
                                                <div class="input-group mb-2 shadow-sm position-relative">
                                                    <span class="input-group-text bg-light text-secondary" style="font-size:0.8rem;">Bait</span>
                                                    <textarea name="dynamic_content[{{ $item->id }}][bait][]" class="form-control" rows="2" placeholder="Ketik lirik secara manual atau ditarik otomatis..." {{ $reqRule }}></textarea>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-light border w-100 fw-medium text-secondary mt-1" onclick="tambahBait({{ $item->id }})">&plus; Tambah Bait Lirik Manual</button>
                                            
                                        @else
                                            @if(str_contains(strtolower($item->title), 'alkitab') || str_contains(strtolower($item->title), 'bacaan'))
                                                <div class="input-group mb-2">
                                                    <input type="text" id="input-alkitab-{{ $item->id }}" class="form-control bg-light" placeholder="Cari Kitab (Contoh: Yohanes 3:16)">
                                                    <button type="button" class="btn btn-secondary fw-medium btn-sm px-4" onclick="tarikAlkitab({{ $item->id }}, event)">Tarik Teks Alkitab</button>
                                                </div>
                                            @endif
                                            
                                            <textarea id="textarea-{{ $item->id }}" name="dynamic_content[{{ $item->id }}]" class="form-control {{ str_contains(strtolower($item->title), 'sikap') || str_contains(strtolower($item->title), 'aksi') ? 'bg-light text-secondary fw-bold' : '' }}" rows="4" placeholder="Ketik teks secara manual..." {{ $reqRule }}>{{ $val }}</textarea>
                                        @endif
                                        
                                    @else
                                        <textarea name="dynamic_content[{{ $item->id }}]" class="form-control text-secondary bg-light" rows="2" readonly>{{ $val }}</textarea>
                                    @endif

                                    <div class="mt-4 pt-3 border-top" style="border-color: #edf2f7 !important;">
                                        <button type="button" class="btn btn-sm btn-outline-primary fw-medium px-4 rounded-pill" onclick="tambahSlideKhusus({{ $item->id }})">
                                            &plus; Sisipkan Slide Tambahan Di Bawah Bagian Ini
                                        </button>
                                        <div id="custom-slide-container-{{ $item->id }}" class="mt-3"></div>
                                    </div>

                                </div>
                            @endforeach

                            <button type="submit" class="btn btn-primary-custom btn-lg w-100 py-3 fw-bold mt-4 shadow text-uppercase" style="letter-spacing: 1px;">Simpan dan Siapkan Penayangan</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function tambahBait(itemId) {
            const container = document.getElementById('bait-container-' + itemId);
            const html = `
                <div class="input-group mb-2 shadow-sm position-relative">
                    <span class="input-group-text bg-light text-secondary" style="font-size:0.8rem;">Bait</span>
                    <textarea name="dynamic_content[${itemId}][bait][]" class="form-control" rows="2" placeholder="Teks lanjutan..."></textarea>
                    <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.parentElement.remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                </div>`;
            container.insertAdjacentHTML('beforeend', html);
        }

        function tambahSlideKhusus(itemId) {
            const container = document.getElementById('custom-slide-container-' + itemId);
            const slideId = Math.random().toString(36).substr(2, 9);
            const html = `
                <div class="p-3 mb-3 border border-2 rounded bg-light position-relative" style="border-color: #cbd5e0 !important;">
                    <span class="badge bg-secondary mb-2">Slide Sisipan Manual</span>
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()"></button>
                    <div class="mb-2">
                        <input type="text" name="custom_slides[${itemId}][${slideId}][title]" class="form-control fw-medium border-secondary" placeholder="Judul Slide (Misal: Pengumuman)" required>
                    </div>
                    <div>
                        <textarea name="custom_slides[${itemId}][${slideId}][content]" class="form-control border-secondary" rows="3" placeholder="Isi konten..." required></textarea>
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
            btn.innerHTML = 'Memuat...'; btn.disabled = true;

            fetch(`/api/fetch-lagu?buku=${buku}&nomor=${nomor}`)
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        judulInput.value = data.judul;
                        container.innerHTML = ''; 
                        
                        const baits = data.text.split('===SLIDE_BREAK===');
                        baits.forEach(bait => {
                            if(bait.trim() !== '') {
                                const html = `
                                    <div class="input-group mb-2 shadow-sm position-relative">
                                        <span class="input-group-text bg-light text-secondary" style="font-size:0.8rem;">Bait</span>
                                        <textarea name="dynamic_content[${itemId}][bait][]" class="form-control" rows="3">${bait.trim()}</textarea>
                                        <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.parentElement.remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                                    </div>`;
                                container.insertAdjacentHTML('beforeend', html);
                            }
                        });
                    } else { alert(data.message); }
                }).catch(err => alert('Gagal menarik data lagu dari Database lokal.')).finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
        }

        function tarikAlkitab(itemId, event) {
            const inputField = document.getElementById('input-alkitab-' + itemId);
            const textarea = document.getElementById('textarea-' + itemId);
            const btn = event.currentTarget;
            const query = inputField.value.trim();
            
            if(!query) { 
                alert('Tulis nama kitab dan pasalnya terlebih dahulu.'); 
                inputField.focus(); return; 
            }
            
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Memuat...'; btn.disabled = true;

            fetch(`/api/fetch-alkitab?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        textarea.value = query.toUpperCase() + "\n===SLIDE_BREAK===\n" + data.text;
                        textarea.classList.add('border-primary');
                    } else { alert(data.message); }
                })
                .catch(err => alert('Terjadi kesalahan koneksi.'))
                .finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
        }
    </script>
</body>
</html>