<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Live Control Panel - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { background: #121212; color: #e0e0e0; height: 100vh; overflow: hidden; font-family: 'Inter', sans-serif; }
        .sidebar { height: 100vh; overflow-y: auto; background: #1a1a1a; border-right: 1px solid #2d2d2d; padding: 20px; }
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background: #444; border-radius: 3px; }
        .card-edit { background: #242424; border: 1px solid #333; border-radius: 6px; padding: 16px; margin-bottom: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);}
        .form-label-header { font-weight: 600; color: #a0aec0; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; margin-bottom: 10px; display: block; border-bottom: 1px solid #333; padding-bottom: 6px; }
        textarea.form-control, input.form-control, select.form-control { background: #181818 !important; color: #cbd5e0 !important; border: 1px solid #333 !important; font-size: 0.85rem; border-radius: 4px;}
        textarea.form-control:focus, input.form-control:focus, select.form-control:focus { border-color: #3182ce !important; box-shadow: none; color: #fff !important; }
        .input-group-text { background: #2d2d2d !important; color: #a0aec0 !important; border-color: #333 !important; font-size: 0.8rem; border-radius: 4px 0 0 4px;}
        .custom-slide-box { background: #1e1e1e; border: 1px solid #4a5568; border-radius: 4px; padding: 10px; margin-top: 10px; border-left: 3px solid #4299e1; }
        .btn-add-slide { font-size: 0.75rem; font-weight: 500; border: 1px dashed #4a5568; color: #a0aec0; background: transparent; padding: 6px; width: 100%; border-radius: 4px; transition: 0.2s;}
        .btn-add-slide:hover { border-color: #cbd5e0; color: #fff; }
        .presenter-view { height: 100vh; background: #0d0d0d; display: flex; flex-direction: column; }
        .preview-header { padding: 12px 24px; background: #141414; border-bottom: 1px solid #2d2d2d; display: flex; justify-content: space-between; align-items: center; }
        .slide-monitor { flex-grow: 1; display: flex; padding: 25px; gap: 25px; justify-content: center; align-items: center; }
        .current-slide-box { width: 55%; aspect-ratio: 16/9; background: #000; border: 1px solid #4299e1; position: relative; display: flex; justify-content: center; align-items: center; text-align: center; padding: 30px; box-shadow: 0 0 20px rgba(66, 153, 225, 0.15); border-radius: 4px;}
        .next-slide-box { width: 25%; aspect-ratio: 16/9; background: #000; border: 1px solid #333; opacity: 0.6; position: relative; display: flex; justify-content: center; align-items: center; text-align: center; padding: 15px; font-size: 0.8vw; color: #a0aec0; border-radius: 4px;}
        .slide-gallery-container { height: 160px; background: #141414; border-top: 1px solid #2d2d2d; overflow-x: auto; overflow-y: hidden; white-space: nowrap; padding: 15px; scroll-behavior: smooth; }
        .slide-gallery-container::-webkit-scrollbar { height: 6px; }
        .slide-gallery-container::-webkit-scrollbar-thumb { background: #444; border-radius: 3px; }
        .slide-thumb { display: inline-block; width: 200px; height: 100%; background: #252526; border: 1px solid #333; margin-right: 15px; padding: 12px; font-size: 0.75rem; color: #a0aec0; cursor: pointer; white-space: normal; overflow: hidden; position: relative; vertical-align: top; transition: 0.2s; border-radius: 4px;}
        .slide-thumb:hover { border-color: #718096; background: #2d2d2d; }
        .slide-thumb.active { border-color: #4299e1; background: #1a202c; color: #fff; box-shadow: 0 0 10px rgba(66, 153, 225, 0.2);}
        .thumb-num { position: absolute; top: 6px; right: 10px; color: #4a5568; font-weight: 700; font-size: 1rem; }
        .thumb-text-content { height: 100%; display: flex; align-items: center; justify-content: center; text-align: center; font-weight: 500; line-height: 1.3; }
        .nav-controls { background: #141414; border-top: 1px solid #2d2d2d; display: flex; justify-content: center; align-items: center; gap: 50px; padding: 12px; }
        .nav-btn { background: #2d2d2d; border: 1px solid #4a5568; color: #e2e8f0; padding: 8px 30px; border-radius: 4px; font-weight: 500; font-size: 0.85rem; transition: 0.2s; letter-spacing: 0.5px;}
        .nav-btn:hover { background: #4a5568; color: #fff; }
        .label-badge { position: absolute; top: 10px; left: 10px; font-size: 0.65rem; font-weight: 600; letter-spacing: 0.5px; padding: 3px 8px; border-radius: 3px; background: #2b6cb0; color: white; }
        .btn-primary-custom { background-color: #2b6cb0; color: white; border: none; font-weight: 600; font-size: 0.85rem; letter-spacing: 0.5px; }
        .btn-primary-custom:hover { background-color: #2c5282; color: white; }
    </style>
</head>
<body onkeydown="handleKeyboard(event)">

<div class="container-fluid p-0">
    <div class="row g-0">
        <div class="col-md-3 sidebar">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="fw-bold m-0 text-secondary text-uppercase" style="letter-spacing: 1px; font-size: 0.8rem;">Control Panel</h6>
                <a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-secondary btn-sm" style="font-size: 0.7rem;">Keluar</a>
            </div>
            
            <form id="liveForm">
                @csrf
                <div class="card-edit" style="border-left: 3px solid #3182ce;">
                    <label class="form-label-header">Desain & Visual Layar</label>
                    
                    <div class="mb-3">
                        <small class="text-secondary d-block mb-1">Jenis Tulisan (Font)</small>
                        <select id="font_family" onchange="updateDesignLive()" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <option value="'Inter', Tahoma, sans-serif">Inter (Modern Default)</option>
                            <option value="Arial, sans-serif">Arial</option>
                            <option value="'Times New Roman', serif">Times New Roman</option>
                            <option value="Georgia, serif">Georgia</option>
                            <option value="'Courier New', monospace">Courier New</option>
                            <option value="Verdana, sans-serif">Verdana</option>
                            <option value="'Trebuchet MS', sans-serif">Trebuchet MS</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <small class="text-secondary d-block mb-1">Gradien Tengah (Center)</small>
                        <input type="color" id="bg_center_color" name="theme_color" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="{{ $schedule->theme_color ?? '#1b2735' }}" style="height: 30px; border:none;">
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <small class="text-secondary d-block mb-1">Gradien Tepi</small>
                            <input type="color" id="bg_edge_color" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="#050505" style="height: 30px; border:none;">
                        </div>
                        <div class="col-6">
                            <small class="text-secondary d-block mb-1">Teks Utama</small>
                            <input type="color" id="text_color" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="#ffffff" style="height: 30px; border:none;">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-secondary d-block mb-1">Ukuran Font (<span id="font_size_val">4.6</span>)</small>
                        <input type="range" id="font_size" oninput="updateDesignLive()" class="form-range" min="3" max="8" step="0.2" value="4.6">
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-secondary d-block mb-1">Warna Bayangan</small>
                            <input type="color" id="shadow_color" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="#000000" style="height: 30px; border:none;">
                        </div>
                        <div class="col-6">
                            <small class="text-secondary d-block mb-1">Tebal Bayangan</small>
                            <input type="range" id="text_shadow" oninput="updateDesignLive()" class="form-range mt-1" min="0" max="1" step="0.1" value="0.9">
                        </div>
                    </div>
                </div>

                <div class="card-edit">
                    <label class="form-label-header">Info Ibadah</label>
                    <div class="mb-2"><input type="text" name="theme" class="form-control" value="{{ $schedule->theme }}" placeholder="Tema Ibadah"></div>
                    <div><input type="text" name="preacher_name" class="form-control" value="{{ $schedule->preacher_name }}" placeholder="Pelayan Firman"></div>
                </div>
                
                @foreach($schedule->liturgy->items as $item)
                    @php 
                        $detail = $schedule->details->where('liturgy_item_id', $item->id)->first();
                        $val = $detail ? $detail->dynamic_content : ($item->static_content ?? '');
                    @endphp
                    
                    <div class="card-edit">
                        <label class="form-label-header" style="color:#e2e8f0;">{{ $item->title }}</label>
                        
                        @if($item->is_dynamic)

                            @if(str_contains(strtolower($item->title), 'sikap') || str_contains(strtolower($item->title), 'aksi'))
                                <select name="dynamic_content[{{ $item->id }}]" class="form-select bg-dark text-white fw-medium">
                                    <option value="(Jemaat Berdiri)" {{ str_contains($val, 'Berdiri') ? 'selected' : '' }}>(Jemaat Berdiri)</option>
                                    <option value="(Jemaat Duduk)" {{ str_contains($val, 'Duduk') && !str_contains($val, 'Teduh') ? 'selected' : '' }}>(Jemaat Duduk)</option>
                                    <option value="(Saat Teduh)" {{ str_contains($val, 'Teduh') ? 'selected' : '' }}>(Saat Teduh / Lilin Dipadamkan)</option>
                                    <option value="(Jemaat Duduk - Calon Sidi Berlutut)" {{ str_contains($val, 'Berlutut') ? 'selected' : '' }}>(Jemaat Duduk - Calon Sidi Berlutut)</option>
                                </select>

                            @elseif(str_contains(strtolower($item->title), 'pra-ibadah') || str_contains(strtolower($item->title), 'prosesi'))
                                <input type="text" name="dynamic_content[{{ $item->id }}][custom_title]" class="form-control mb-2 fw-medium text-info" value="{{ is_array($val) ? ($val['custom_title'] ?? '') : str_replace(' (Opsional)', '', $item->title) }}">
                                <textarea name="dynamic_content[{{ $item->id }}][content]" class="form-control" rows="3">{{ is_array($val) ? ($val['content'] ?? '') : (is_string($val) ? $val : '') }}</textarea>
                                
                            @elseif(str_contains(strtolower($item->title), 'nyanyian') || str_contains(strtolower($item->title), 'pujian'))
                                <div class="input-group mb-2">
                                    <select id="buku-lagu-{{ $item->id }}" class="form-select bg-dark text-white" style="max-width: 90px;">
                                        <option value="KJ">KJ</option>
                                        <option value="NKB">NKB</option>
                                        <option value="PKJ">PKJ</option>
                                        <option value="NR">NR</option>
                                        <option value="BEBAS">Lainnya</option>
                                    </select>
                                    <input type="text" id="nomor-lagu-{{ $item->id }}" class="form-control" placeholder="No. Lagu">
                                    <button type="button" class="btn btn-secondary btn-sm px-3" onclick="tarikLagu({{ $item->id }}, event)">Tarik</button>
                                </div>

                                <input type="text" name="dynamic_content[{{ $item->id }}][judul]" class="form-control mb-2 fw-medium" placeholder="Judul Lagu" value="{{ is_array($val) ? ($val['judul'] ?? '') : '' }}">
                                <div id="bait-container-{{ $item->id }}">
                                    @if(is_array($val) && isset($val['bait']) && is_array($val['bait']))
                                        @foreach($val['bait'] as $bait)
                                            <div class="input-group mb-2 position-relative">
                                                <span class="input-group-text">Bait</span>
                                                <textarea name="dynamic_content[{{ $item->id }}][bait][]" class="form-control" rows="2">{{ $bait }}</textarea>
                                                <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3" onclick="this.parentElement.remove()" style="font-size: 14px;">&times;</button>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="input-group mb-2"><span class="input-group-text">Bait</span><textarea name="dynamic_content[{{ $item->id }}][bait][]" class="form-control" rows="2"></textarea></div>
                                    @endif
                                </div>
                                <button type="button" class="btn-add-slide mt-1" onclick="tambahBait({{ $item->id }})">&plus; Tambah Lirik</button>
                                
                            @else
                                @if(str_contains(strtolower($item->title), 'alkitab') || str_contains(strtolower($item->title), 'bacaan'))
                                    <div class="input-group mb-2"><input type="text" id="input-alkitab-{{ $item->id }}" class="form-control" placeholder="Cari Kitab"><button type="button" class="btn btn-secondary btn-sm px-3" onclick="tarikAlkitab({{ $item->id }}, event)">Tarik</button></div>
                                @endif
                                <textarea id="textarea-{{ $item->id }}" name="dynamic_content[{{ $item->id }}]" class="form-control" rows="3">{{ is_array($val) ? ($val['content'] ?? '') : $val }}</textarea>
                            @endif
                            
                        @else
                            <textarea name="dynamic_content[{{ $item->id }}]" class="form-control text-secondary" style="background:#1a1a1a!important;" rows="2" readonly>{{ $item->static_content }}</textarea>
                        @endif

                        <div class="mt-3 pt-3 border-top" style="border-color: #333 !important;">
                            <div id="custom-slide-container-{{ $item->id }}">
                                @if(isset($schedule->customSlides) && $schedule->customSlides->where('liturgy_item_id', $item->id)->count() > 0)
                                    @foreach($schedule->customSlides->where('liturgy_item_id', $item->id) as $index => $cSlide)
                                        <div class="custom-slide-box">
                                            <input type="text" name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][title]" class="form-control form-control-sm mb-2 fw-medium text-info" value="{{ $cSlide->title }}">
                                            <textarea name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][content]" class="form-control form-control-sm" rows="2">{{ $cSlide->content }}</textarea>
                                            <button type="button" class="btn btn-sm text-danger mt-1 p-0" style="font-size:0.75rem;" onclick="this.parentElement.remove()">Hapus Sisipan</button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn-add-slide mt-2" onclick="tambahSlideKhusus({{ $item->id }})">&plus; Sisipkan Slide Tambahan</button>
                        </div>
                    </div>
                @endforeach
                
                <button type="button" onclick="updateLive()" class="btn btn-primary-custom w-100 py-3 mt-3 shadow sticky-bottom" style="bottom: 15px;">SIMPAN & SINKRONISASI</button>
            </form>
        </div>

        <div class="col-md-9 presenter-view">
            <div class="preview-header">
                <span class="fw-medium text-info" style="font-size: 0.85rem; letter-spacing: 0.5px;">LIVE STATUS: {{ strtoupper($schedule->liturgy->name) }}</span>
                <button onclick="openProjector()" class="btn btn-primary-custom btn-sm px-4">BUKA PROYEKTOR</button>
            </div>

            <div class="slide-monitor">
                <div class="current-slide-box">
                    <span class="label-badge">TAMPILAN SAAT INI</span>
                    <div id="current-text" class="fw-bold" style="font-size: 1.5vw; line-height: 1.4; color: #fff;">Memuat...</div>
                </div>
                <div class="next-slide-box">
                    <span class="label-badge" style="background: #4a5568;">BERIKUTNYA</span>
                    <div id="next-text">Selesai</div>
                </div>
            </div>

            <div class="slide-gallery-container" id="slideGallery"></div>

            <div class="nav-controls">
                <button class="nav-btn" onclick="controlProjector('prev')">SEBELUMNYA</button>
                <div class="text-center">
                    <div id="slide-num" class="fw-bold fs-5 text-white">0 / 0</div>
                    <div style="font-size: 0.65rem; color: #718096; margin-top: 2px;">Gunakan Panah Kiri/Kanan atau Atas/Bawah</div>
                </div>
                <button class="nav-btn" onclick="controlProjector('next')">SELANJUTNYA</button>
            </div>
        </div>
    </div>
</div>

<script>
    function updateDesignLive() {
        const sizeVal = document.getElementById('font_size').value; 
        document.getElementById('font_size_val').innerText = sizeVal;

        const designSettings = { 
            fontFamily: document.getElementById('font_family').value,
            bgCenterColor: document.getElementById('bg_center_color').value,
            bgEdgeColor: document.getElementById('bg_edge_color').value, 
            textColor: document.getElementById('text_color').value, 
            fontSize: sizeVal + 'vw',
            shadowColor: document.getElementById('shadow_color').value,
            textShadow: document.getElementById('text_shadow').value
        };
        localStorage.setItem('live_design_settings', JSON.stringify(designSettings));
    }

    function tambahBait(itemId) {
        const container = document.getElementById('bait-container-' + itemId);
        const html = `<div class="input-group mb-2 position-relative"><span class="input-group-text">Bait</span><textarea name="dynamic_content[${itemId}][bait][]" class="form-control" rows="2"></textarea><button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3" onclick="this.parentElement.remove()" style="font-size: 14px;">&times;</button></div>`;
        container.insertAdjacentHTML('beforeend', html);
    }

    function tambahSlideKhusus(itemId) {
        const container = document.getElementById('custom-slide-container-' + itemId); const slideId = Math.random().toString(36).substr(2, 9);
        const html = `<div class="custom-slide-box"><input type="text" name="custom_slides[${itemId}][${slideId}][title]" class="form-control form-control-sm mb-2 fw-medium text-info" placeholder="Judul Sisipan"><textarea name="custom_slides[${itemId}][${slideId}][content]" class="form-control form-control-sm" rows="2" placeholder="Isi teks..."></textarea><button type="button" class="btn btn-sm text-danger mt-1 p-0" style="font-size:0.75rem;" onclick="this.parentElement.remove()">Hapus Sisipan</button></div>`;
        container.insertAdjacentHTML('beforeend', html); container.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
                    baits.forEach(bait => {
                        if(bait.trim() !== '') {
                            const html = `<div class="input-group mb-2 position-relative"><span class="input-group-text">Bait</span><textarea name="dynamic_content[${itemId}][bait][]" class="form-control" rows="3">${bait.trim()}</textarea><button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3" onclick="this.parentElement.remove()" style="font-size: 14px;">&times;</button></div>`;
                            container.insertAdjacentHTML('beforeend', html);
                        }
                    });
                } else { alert(data.message); }
            }).catch(err => alert('Gagal menarik data lagu.')).finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
    }

    function tarikAlkitab(itemId, event) {
        const inputField = document.getElementById('input-alkitab-' + itemId); const textarea = document.getElementById('textarea-' + itemId);
        const btn = event.currentTarget; const query = inputField.value.trim();
        if(!query) return alert('Masukkan kitab dan pasal.'); 
        const originalText = btn.innerHTML; btn.innerHTML = '...'; btn.disabled = true;
        fetch(`/api/fetch-alkitab?q=${encodeURIComponent(query)}`).then(res => res.json()).then(data => {
            if(data.success) { textarea.value = query.toUpperCase() + "\n===SLIDE_BREAK===\n" + data.text; } else alert(data.message);
        }).catch(() => alert('Terjadi kesalahan koneksi.')).finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
    }
    
    function openProjector() {
        window.open("{{ route('liturgy.presentation', $schedule->id) }}", "ProjectorWindow", `width=${window.screen.width},height=${window.screen.height},left=${window.screen.width},top=0,menubar=no,toolbar=no,location=no,status=no`);
    }

    function controlProjector(action, specificIndex = null) {
        localStorage.setItem('projector_command', JSON.stringify({ action: action, index: specificIndex, time: Date.now() }));
        setTimeout(updateConsoleView, 50);
    }

    function handleKeyboard(e) {
        if (['TEXTAREA', 'INPUT', 'SELECT'].includes(document.activeElement.tagName)) return;
        if (['ArrowRight', 'ArrowUp', ' '].includes(e.key)) { e.preventDefault(); controlProjector('next'); }
        if (['ArrowLeft', 'ArrowDown'].includes(e.key)) { e.preventDefault(); controlProjector('prev'); }
    }

    function updateLive() {
        const btn = document.querySelector('.btn-primary-custom'); const originalText = btn.innerText; btn.innerText = "MENYIMPAN..."; btn.disabled = true;
        fetch("{{ route('liturgy.update', $schedule->id) }}", { method: 'POST', body: new FormData(document.getElementById('liveForm')), headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
        .then(() => { localStorage.setItem('liturgy_update', Date.now()); setTimeout(() => { btn.innerText = originalText; btn.disabled = false; location.reload(); }, 600); });
    }

    function updateConsoleView() {
        const current = localStorage.getItem('last_slide_text') || '...'; const next = localStorage.getItem('next_slide_text') || 'Selesai';
        const index = parseInt(localStorage.getItem('last_slide_index')) || 0; const total = parseInt(localStorage.getItem('total_slides')) || 0;
        document.getElementById('current-text').innerText = current; document.getElementById('next-text').innerText = next; document.getElementById('slide-num').innerText = `${index + 1} / ${total}`;
        document.querySelectorAll('.slide-thumb').forEach((thumb, i) => { thumb.classList.toggle('active', i === index); if(i === index) thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' }); });
    }

    function buildGallery() {
        const gallery = document.getElementById('slideGallery'); gallery.innerHTML = '';
        const allSlidesData = JSON.parse(localStorage.getItem('all_slides_content')) || []; const total = parseInt(localStorage.getItem('total_slides')) || allSlidesData.length;
        for (let i = 0; i < total; i++) {
            const thumb = document.createElement('div'); thumb.className = 'slide-thumb';
            thumb.innerHTML = `<span class="thumb-num">${i+1}</span><div class="thumb-text-content">${allSlidesData[i] ? allSlidesData[i].substring(0, 60) + '...' : `Slide ${i+1}`}</div>`;
            thumb.ondblclick = () => controlProjector('jump', i); gallery.appendChild(thumb);
        }
    }

    window.addEventListener('storage', (e) => { if (e.key === 'slide_changed') updateConsoleView(); if (e.key === 'all_slides_content') buildGallery(); });
    window.onload = () => { buildGallery(); updateConsoleView(); updateDesignLive(); };
</script>
</body>
</html>