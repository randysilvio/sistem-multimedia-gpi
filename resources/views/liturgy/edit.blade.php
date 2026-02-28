<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Ibadah - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');
        body { background-color: #f4f6f8; font-family: 'Inter', sans-serif; color: #2d3748; }
        .navbar { background-color: #1a202c !important; }
        .navbar-brand { font-weight: 600; letter-spacing: 1px; font-size: 1.1rem; }
        .form-control:focus, .form-select:focus { border-color: #3182ce; box-shadow: 0 0 0 0.2rem rgba(49, 130, 206, 0.15); }
        .btn-primary-custom { background-color: #2b6cb0; color: white; border: none; }
        .btn-primary-custom:hover { background-color: #2c5282; color: white; }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-dark shadow-sm mb-4 py-3">
        <div class="container">
            <a class="navbar-brand text-uppercase" href="#">Sistem Multimedia GPI Papua</a>
            <a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-light btn-sm fw-medium px-4">Kembali ke Galeri</a>
        </div>
    </nav>

    <div class="container pb-5" style="max-width: 900px;">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white p-4 border-bottom">
                <h5 class="mb-0 fw-bold text-uppercase" style="color: #2d3748; letter-spacing: 1px;">Edit Jadwal: {{ $liturgy->name }}</h5>
            </div>
            <div class="card-body bg-white p-4">
                <form action="{{ route('liturgy.update', $schedule->id) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="row mb-4 bg-light p-4 rounded border" style="border-color: #e2e8f0 !important;">
                        <div class="col-md-3">
                            <label class="form-label fw-medium text-secondary small">Tanggal Ibadah</label>
                            <input type="date" name="worship_date" class="form-control" value="{{ $schedule->worship_date }}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-medium text-secondary small">Tema</label>
                            <input type="text" name="theme" class="form-control" value="{{ $schedule->theme }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-secondary small">Pelayan Firman</label>
                            <input type="text" name="preacher_name" class="form-control" value="{{ $schedule->preacher_name }}">
                        </div>
                    </div>

                    @foreach($liturgyItems as $item)
                        @php
                            $detail = $scheduleDetails->where('liturgy_item_id', $item->id)->first();
                            $content = $detail ? $detail->dynamic_content : $item->static_content;
                        @endphp

                        <div class="mb-4 p-4 border rounded shadow-sm bg-white" style="border-color: #e2e8f0 !important;">
                            <label class="form-label fw-bold text-primary fs-6 text-uppercase" style="letter-spacing: 0.5px;">{{ $item->title }}</label>
                            
                            @if(is_array($content))
                                @if(str_contains(strtolower($item->title), 'nyanyian') || str_contains(strtolower($item->title), 'pujian'))
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
                                    <input type="text" name="dynamic_content[{{ $item->id }}][judul]" class="form-control mb-2 fw-medium" placeholder="Judul Lagu" value="{{ $content['judul'] ?? '' }}">
                                    <div id="bait-container-{{ $item->id }}">
                                        @if(isset($content['bait']))
                                            @foreach($content['bait'] as $bait)
                                                <div class="input-group mb-2 position-relative">
                                                    <span class="input-group-text bg-light text-secondary" style="font-size:0.8rem;">Bait</span>
                                                    <textarea name="dynamic_content[{{ $item->id }}][bait][]" class="form-control" rows="2">{{ $bait }}</textarea>
                                                    <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3" onclick="this.parentElement.remove()" style="font-size: 14px;">&times;</button>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="input-group mb-2 position-relative">
                                                <span class="input-group-text bg-light text-secondary" style="font-size:0.8rem;">Bait</span>
                                                <textarea name="dynamic_content[{{ $item->id }}][bait][]" class="form-control" rows="2" placeholder="Teks lirik..."></textarea>
                                            </div>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light border w-100 fw-medium text-secondary mt-1" onclick="tambahBait({{ $item->id }})">&plus; Tambah Bait Lirik</button>
                                @else
                                    <div class="mb-3"><input type="text" name="dynamic_content[{{ $item->id }}][custom_title]" class="form-control fw-medium" value="{{ $content['custom_title'] ?? '' }}"></div>
                                    <textarea name="dynamic_content[{{ $item->id }}][content]" class="form-control" rows="3">{{ $content['content'] ?? '' }}</textarea>
                                @endif
                            @else
                                @if(str_contains(strtolower($item->title), 'sikap') || str_contains(strtolower($item->title), 'aksi'))
                                    <select name="dynamic_content[{{ $item->id }}]" class="form-select bg-light fw-medium text-secondary">
                                        <option value="(Jemaat Berdiri)" {{ str_contains($content, 'Berdiri') ? 'selected' : '' }}>(Jemaat Berdiri)</option>
                                        <option value="(Jemaat Duduk)" {{ str_contains($content, 'Duduk') && !str_contains($content, 'Teduh') ? 'selected' : '' }}>(Jemaat Duduk)</option>
                                        <option value="(Saat Teduh)" {{ str_contains($content, 'Teduh') ? 'selected' : '' }}>(Saat Teduh / Lilin Dipadamkan)</option>
                                        <option value="(Jemaat Duduk - Calon Sidi Berlutut)" {{ str_contains($content, 'Berlutut') ? 'selected' : '' }}>(Jemaat Duduk - Calon Sidi Berlutut)</option>
                                    </select>
                                @else
                                    <textarea name="dynamic_content[{{ $item->id }}]" class="form-control" rows="4">{{ $content }}</textarea>
                                @endif
                            @endif
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-primary-custom w-100 py-3 fs-6 fw-bold mt-2 shadow-sm" style="letter-spacing: 1px;">SIMPAN PERUBAHAN JADWAL</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function tambahBait(itemId) {
            const container = document.getElementById('bait-container-' + itemId);
            const html = `<div class="input-group mb-2 shadow-sm position-relative"><span class="input-group-text bg-light border-end-0 text-secondary" style="font-size:0.8rem;">Bait</span><textarea name="dynamic_content[${itemId}][bait][]" class="form-control" rows="2" placeholder="Teks lanjutan..."></textarea><button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.parentElement.remove()">&times;</button></div>`;
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
                                const html = `<div class="input-group mb-2 shadow-sm position-relative"><span class="input-group-text bg-light border-end-0 text-secondary" style="font-size:0.8rem;">Bait</span><textarea name="dynamic_content[${itemId}][bait][]" class="form-control" rows="3">${bait.trim()}</textarea><button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.parentElement.remove()">&times;</button></div>`;
                                container.insertAdjacentHTML('beforeend', html);
                            }
                        });
                    } else { alert(data.message); }
                }).catch(err => alert('Gagal menarik data lagu.')).finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
        }
    </script>
</body>
</html>