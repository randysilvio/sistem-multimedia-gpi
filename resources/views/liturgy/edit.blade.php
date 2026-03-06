<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jadwal Ibadah - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; color: #1e293b; padding-bottom: 120px; }
        .navbar { background-color: #0f172a !important; }
        .builder-container { max-width: 800px; margin: auto; margin-top: 30px; }
        
        .block-card { background: white; border-radius: 8px; padding: 25px; margin-bottom: 20px; border: 1px solid #e2e8f0; position: relative; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: 0.2s; }
        .block-card:focus-within { box-shadow: 0 4px 15px rgba(43, 108, 176, 0.15); border-color: #90cdf4; }
        
        .btn-delete-block { position: absolute; top: 15px; right: 15px; background: transparent; border: 1px solid #e2e8f0; color: #ef4444; font-size: 1.2rem; cursor: pointer; width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; transition: 0.2s; line-height: 1; }
        .btn-delete-block:hover { background: #fee2e2; border-color: #fca5a5; transform: scale(1.05); }

        /* SOLID BOTTOM TOOLBAR PROFESIONAL */
        .toolbar-menu { position: fixed; bottom: 0; left: 0; width: 100%; background: #ffffff; padding: 15px 0; border-top: 1px solid #e2e8f0; box-shadow: 0 -4px 20px rgba(0,0,0,0.04); z-index: 900; }
        
        .btn-primary-custom { background-color: #0f172a; color: white; border: none; font-weight: 600; letter-spacing: 0.5px; padding: 10px 30px; border-radius: 6px; transition: 0.2s;}
        .btn-primary-custom:hover { background-color: #1e293b; color: white; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm py-3 mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-uppercase m-0 d-flex align-items-center" href="{{ route('liturgy.gallery') }}">
                <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" alt="Logo GPI" height="32" class="me-3">
                <span>Edit Presentasi Jadwal</span>
            </a>
            <a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-light btn-sm fw-medium px-4">Batal & Kembali</a>
        </div>
    </nav>

    <div class="container builder-container">

        @if(session('success'))
            <div class="alert alert-success shadow-sm border-0 border-start border-4 border-success mb-4">
                <strong>Tersimpan!</strong> {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('liturgy.update', $schedule->id) }}" method="POST" id="editForm">
            @csrf

            <div class="card border-0 shadow-sm mb-4 rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3 text-uppercase" style="letter-spacing: 1px; font-size:0.85rem;">Info Dasar & Tema</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Tanggal Ibadah</label>
                            <input type="text" class="form-control bg-light fw-bold" value="{{ \Carbon\Carbon::parse($schedule->worship_date)->translatedFormat('l, d F Y') }}" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Tema / Sub Tema</label>
                            <input type="text" name="theme" class="form-control bg-light" value="{{ $schedule->theme }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-secondary">Pelayan Firman</label>
                            <input type="text" name="preacher_name" class="form-control bg-light" value="{{ $schedule->preacher_name }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Warna Tema Utama</label>
                            <input type="color" name="theme_color" class="form-control form-control-color w-100 bg-light" value="{{ $schedule->theme_color ?? '#1b2735' }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-end mt-5 mb-3">
                <h6 class="fw-bold text-dark text-uppercase m-0" style="letter-spacing: 1px; font-size:0.85rem;">Data Teks & Lirik (Bisa Diubah)</h6>
                <small class="text-muted">{{ $schedule->liturgy->name ?? 'Kustom' }}</small>
            </div>

            @foreach($schedule->liturgy->items as $item)
                @php 
                    $detail = $schedule->details->where('liturgy_item_id', $item->id)->first();
                    $val = $detail ? $detail->dynamic_content : ($item->static_content ?? '');
                    
                    // Deteksi Tipe Warna
                    $titleLower = strtolower($item->title);
                    $cardColor = '#718096'; $typeLabel = 'TEKS BEBAS';
                    if (str_contains($titleLower, 'nyanyian') || str_contains($titleLower, 'pujian')) { $cardColor = '#2b6cb0'; $typeLabel = 'NYANYIAN JEMAAT'; } 
                    elseif (str_contains($titleLower, 'alkitab') || str_contains($titleLower, 'bacaan')) { $cardColor = '#2c5282'; $typeLabel = 'BACAAN ALKITAB'; } 
                    elseif (str_contains($titleLower, 'votum') || str_contains($titleLower, 'prosesi') || str_contains($titleLower, 'pengakuan')) { $cardColor = '#4a5568'; $typeLabel = 'VOTUM / PROSESI / PENGAKUAN'; } 
                    elseif (str_contains($titleLower, 'sikap') || str_contains($titleLower, 'aksi')) { $cardColor = '#c53030'; $typeLabel = 'INSTRUKSI SIKAP JEMAAT'; }
                @endphp
                
                <div class="block-card" style="border-top: 4px solid {{ $cardColor }};">
                    <div class="mb-3 pb-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="badge text-uppercase" style="background-color: {{ $cardColor }}; letter-spacing: 0.5px;">{{ $typeLabel }}</span>
                        <span class="fw-bold text-dark">{{ $item->title }}</span>
                    </div>
                    
                    @if($item->is_dynamic)

                        @if(str_contains(strtolower($item->title), 'sikap') || str_contains(strtolower($item->title), 'aksi'))
                            @php $sikapVal = is_array($val) ? ($val['content'] ?? $val[0] ?? '') : $val; @endphp
                            <select name="dynamic_content[{{ $item->id }}]" class="form-select bg-light fw-bold border-secondary text-dark">
                                <option value="(Jemaat Berdiri)" {{ str_contains($sikapVal, 'Berdiri') ? 'selected' : '' }}>(Jemaat Berdiri)</option>
                                <option value="(Jemaat Duduk)" {{ str_contains($sikapVal, 'Duduk') && !str_contains($sikapVal, 'Teduh') ? 'selected' : '' }}>(Jemaat Duduk)</option>
                                <option value="(Saat Teduh)" {{ str_contains($sikapVal, 'Teduh') ? 'selected' : '' }}>(Saat Teduh / Lilin Dipadamkan)</option>
                            </select>

                        @elseif(str_contains(strtolower($item->title), 'pra-ibadah') || str_contains(strtolower($item->title), 'prosesi'))
                            <input type="text" name="dynamic_content[{{ $item->id }}][custom_title]" class="form-control mb-2 fw-medium" value="{{ is_array($val) ? ($val['custom_title'] ?? '') : str_replace(' (Opsional)', '', $item->title) }}" placeholder="Judul Tampilan">
                            <textarea name="dynamic_content[{{ $item->id }}][content]" class="form-control bg-light" rows="4">{{ is_array($val) ? ($val['content'] ?? '') : (is_string($val) ? $val : '') }}</textarea>
                            
                        @elseif(str_contains(strtolower($item->title), 'nyanyian') || str_contains(strtolower($item->title), 'pujian'))
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

                            <input type="text" name="dynamic_content[{{ $item->id }}][judul]" class="form-control mb-3 fw-bold text-primary" placeholder="Judul Lagu" value="{{ is_array($val) ? ($val['judul'] ?? '') : '' }}">
                            
                            <div id="bait-container-{{ $item->id }}">
                                @if(is_array($val) && isset($val['bait']) && is_array($val['bait']))
                                    @php $verseCountForEdit = 1; @endphp
                                    @foreach($val['bait'] as $idx => $bait)
                                        @php 
                                            $isReffKey = (is_string($idx) && stripos($idx, 'ref') !== false) || preg_match('/^\[?reff?\]?[\s\:\.\-]?/i', $bait);
                                            $displayKey = $isReffKey ? 'Reff' : 'Bait ' . $verseCountForEdit;
                                            if (!$isReffKey) $verseCountForEdit++;
                                            $cleanTextForEdit = preg_replace('/^\[?REFF\]?\s*/i', '', $bait);
                                        @endphp
                                        <div class="input-group mb-2 shadow-sm position-relative bait-item">
                                            <span class="input-group-text {{ $isReffKey ? 'bg-warning text-dark' : 'bg-light text-secondary' }} fw-bold" style="font-size:0.8rem; min-width: 70px; justify-content:center;">{{ $displayKey }}</span>
                                            <textarea name="dynamic_content[{{ $item->id }}][bait][{{ $idx }}]" class="form-control" rows="3">{{ $isReffKey ? '[REFF] ' . trim($cleanTextForEdit) : trim($cleanTextForEdit) }}</textarea>
                                            <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="hapusBaitEdit(this, {{ $item->id }})" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="tambahBaitEdit({{ $item->id }})">&plus; Tambah Bait Lirik</button>
                            
                        @else
                            @if(str_contains(strtolower($item->title), 'alkitab') || str_contains(strtolower($item->title), 'bacaan'))
                                <div class="row g-2 mb-2">
                                    <div class="col-md-10">
                                        <input type="text" id="input-alkitab-{{ $item->id }}" class="form-control bg-light" placeholder="Cari Kitab (Cth: Yohanes 3:16)">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-secondary w-100" onclick="tarikAlkitab({{ $item->id }}, event)">Tarik</button>
                                    </div>
                                </div>
                            @endif
                            <textarea id="textarea-{{ $item->id }}" name="dynamic_content[{{ $item->id }}]" class="form-control bg-light" rows="4">{{ is_array($val) ? ($val['content'] ?? '') : $val }}</textarea>
                        @endif
                        
                    @else
                        <textarea name="dynamic_content[{{ $item->id }}]" class="form-control text-secondary bg-light border-0" rows="2" readonly>{{ $item->static_content }}</textarea>
                    @endif

                    <div class="mt-4 pt-3 border-top" style="border-color: #edf2f7 !important;">
                        <label class="form-label text-secondary"><small>Slide Tambahan / Sisipan (Opsional)</small></label>
                        <div id="custom-slide-container-{{ $item->id }}">
                            @if(isset($schedule->customSlides) && $schedule->customSlides->where('liturgy_item_id', $item->id)->count() > 0)
                                @foreach($schedule->customSlides->where('liturgy_item_id', $item->id) as $index => $cSlide)
                                    <div class="p-3 mb-2 border rounded bg-light position-relative">
                                        <button type="button" class="btn-delete-block" onclick="this.parentElement.remove()" style="top:5px; right:5px;">&times;</button>
                                        <div class="mb-2 pe-4">
                                            <input type="text" name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][title]" class="form-control fw-medium" value="{{ $cSlide->title }}">
                                        </div>
                                        <textarea name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][content]" class="form-control" rows="3">{{ $cSlide->content }}</textarea>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-light border fw-medium px-4 rounded mt-2" onclick="tambahSlideKhusus({{ $item->id }})">
                            &plus; Sisipkan Slide Tambahan Di Sini
                        </button>
                    </div>
                </div>
            @endforeach
            
            <div class="toolbar-menu">
                <div class="container d-flex justify-content-end">
                    <button type="submit" form="editForm" class="btn btn-primary-custom">UPDATE JADWAL IBADAH</button>
                </div>
            </div>
        </form>
    </div>

<script>
    // FUNGSI REINDEX BAIT SEPERTI DI CONTROL PANEL
    function reindexBaitEdit(container) {
        let bCount = 1;
        container.querySelectorAll('.bait-item').forEach(el => {
            let span = el.querySelector('span');
            if (span && !span.innerText.toLowerCase().includes('reff')) {
                span.innerText = 'Bait ' + bCount;
                bCount++;
            }
        });
    }

    function hapusBaitEdit(btn, itemId) {
        const container = btn.closest('#bait-container-' + itemId);
        btn.closest('.bait-item').remove();
        reindexBaitEdit(container);
    }

    function tambahBaitEdit(itemId) {
        const container = document.getElementById('bait-container-' + itemId);
        const uniqueKey = 'b_' + Date.now().toString().slice(-5);
        
        const html = `
            <div class="input-group mb-2 shadow-sm position-relative bait-item">
                <span class="input-group-text bg-light text-secondary fw-bold" style="font-size:0.8rem; min-width: 70px; justify-content:center;">Bait</span>
                <textarea name="dynamic_content[${itemId}][bait][${uniqueKey}]" class="form-control" rows="3"></textarea>
                <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="hapusBaitEdit(this, ${itemId})" style="font-size: 14px; padding: 2px 6px;">&times;</button>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        reindexBaitEdit(container);
    }

    function tambahSlideKhusus(itemId) {
        const container = document.getElementById('custom-slide-container-' + itemId); 
        const slideId = Math.random().toString(36).substr(2, 9);
        const html = `
            <div class="p-3 mb-2 border rounded bg-light position-relative">
                <button type="button" class="btn-delete-block" onclick="this.parentElement.remove()" style="top:5px; right:5px;">&times;</button>
                <div class="mb-2 pe-4">
                    <input type="text" name="custom_slides[${itemId}][${slideId}][title]" class="form-control fw-medium" placeholder="Judul Sisipan">
                </div>
                <textarea name="custom_slides[${itemId}][${slideId}][content]" class="form-control" rows="3" placeholder="Isi teks slide..."></textarea>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }
    
    function tarikLagu(itemId, event) {
        const buku = document.getElementById(`buku-lagu-${itemId}`).value;
        const nomor = document.getElementById(`nomor-lagu-${itemId}`).value.trim();
        const judulInput = document.querySelector(`input[name="dynamic_content[${itemId}][judul]"]`);
        const container = document.getElementById(`bait-container-${itemId}`);
        const btn = event.currentTarget;
        
        if(!nomor) return alert('Masukkan nomor lagu!');
        
        btn.innerHTML = '...'; btn.disabled = true;

        fetch(`/api/fetch-lagu?buku=${buku}&nomor=${nomor}`)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    if(judulInput) judulInput.value = data.judul;
                    container.innerHTML = ''; 
                    const baits = data.text.split('===SLIDE_BREAK===');
                    
                    baits.forEach((bait, idx) => {
                        let baitRaw = bait.trim();
                        if(baitRaw !== '') {
                            let isReff = baitRaw.toUpperCase().startsWith('[REFF]') || baitRaw.toLowerCase().startsWith('reff') || baitRaw.toLowerCase().startsWith('ref');
                            let cleanBait = baitRaw.replace(/^\[?REFF\]?\s*/i, '');
                            let uniqueKey = isReff ? 'ref_' + idx : 'b_' + idx;
                            let labelText = isReff ? 'Reff' : 'Bait';

                            const html = `
                                <div class="input-group mb-2 shadow-sm position-relative bait-item">
                                    <span class="input-group-text ${isReff ? 'bg-warning text-dark' : 'bg-light text-secondary'} fw-bold" style="font-size:0.8rem; min-width: 70px; justify-content:center;">${labelText}</span>
                                    <textarea name="dynamic_content[${itemId}][bait][${uniqueKey}]" class="form-control" rows="3">${isReff ? '[REFF]\n' + cleanBait : cleanBait}</textarea>
                                    <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="hapusBaitEdit(this, ${itemId})" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                                </div>`;
                            container.insertAdjacentHTML('beforeend', html);
                        }
                    });
                    reindexBaitEdit(container);
                } else { alert(data.message); }
            }).finally(() => { btn.innerHTML = 'Tarik'; btn.disabled = false; });
    }

    function tarikAlkitab(itemId, event) {
        const inputField = document.getElementById('input-alkitab-' + itemId); 
        const textarea = document.getElementById('textarea-' + itemId);
        const btn = event.currentTarget; 
        const query = inputField.value.trim();
        
        if(!query) return alert('Masukkan kitab dan pasal.'); 
        
        btn.innerHTML = '...'; btn.disabled = true;
        
        fetch(`/api/fetch-alkitab?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if(data.success) { 
                    textarea.value = query.toUpperCase() + "\n===SLIDE_BREAK===\n" + data.text; 
                } else { alert(data.message); }
            }).finally(() => { btn.innerHTML = 'Tarik'; btn.disabled = false; });
    }
</script>
</body>
</html>