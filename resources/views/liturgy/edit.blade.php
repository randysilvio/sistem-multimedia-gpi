@extends('layouts.admin')

@section('eyebrow', 'Jadwal & Presentasi')
@section('title')
Edit Jadwal: {{ $schedule->theme ?: ($schedule->liturgy->name ?? 'Ibadah') }}
@endsection

@section('actions')
<a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-secondary">Kembali</a>
<a href="{{ route('liturgy.bulletin', $schedule->id) }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">Cetak Buletin</a>
<a href="{{ route('liturgy.control', $schedule->id) }}" class="btn btn-primary">Buka Control Panel</a>
@endsection

@section('content')
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


    <div class="container builder-container">

        <form action="{{ route('liturgy.update', $schedule->id) }}" method="POST" id="editForm">
            @csrf

            <div class="card border-0 shadow-sm mb-4 rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3 text-uppercase" style="letter-spacing: 1px; font-size:0.85rem;">Informasi Dasar Ibadah</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Tanggal Ibadah</label>
                            <input type="text" class="form-control bg-light" value="{{ \Carbon\Carbon::parse($schedule->worship_date)->translatedFormat('l, d F Y') }}" readonly disabled>
                            <small class="text-muted" style="font-size: 0.7rem;">(Tanggal tidak bisa diubah)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Tema / Sub Tema (Opsional)</label>
                            <input type="text" name="theme" class="form-control bg-white" placeholder="Cth: Ibadah Raya" value="{{ $schedule->theme }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-secondary">Pelayan Firman</label>
                            <input type="text" name="preacher_name" class="form-control bg-white" placeholder="Nama Pelayan" value="{{ $schedule->preacher_name }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Warna Tema Utama</label>
                            <input type="color" name="theme_color" class="form-control form-control-color w-100 bg-white" value="{{ $schedule->theme_color ?? '#1b2735' }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-end mt-5 mb-3">
                <h6 class="fw-bold text-dark text-uppercase m-0" style="letter-spacing: 1px; font-size:0.85rem;">Pengisian Teks & Lirik Slide</h6>
                <small class="text-muted">Liturgi: {{ $schedule->liturgy->name }}</small>
            </div>

            @foreach($schedule->liturgy->items as $item)
                @php 
                    $detail = $schedule->details->where('liturgy_item_id', $item->id)->first();
                    $val = $detail ? $detail->dynamic_content : ($item->static_content ?? '');
                    
                    $titleLower = strtolower($item->title);
                    $cleanTitle = cleanSlideTitle($item->title);
                    $useCamera = is_array($val) && isset($val['use_camera']) && $val['use_camera'] == true;

                    $type = 'teks_bebas';
                    $cardColor = '#718096'; 
                    $typeLabel = 'TEKS BEBAS / INSTRUKSI'; 

                    if (str_contains($titleLower, 'nyanyian') || str_contains($titleLower, 'pujian')) { 
                        $type = 'nyanyian'; 
                        $cardColor = '#2b6cb0'; 
                        $typeLabel = 'NYANYIAN JEMAAT'; 
                    } elseif (\App\Services\SlideBuilder::itemType($item->title) === 'alkitab') { 
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
                            <div class="row g-2 mb-2 song-ref">
                                <div class="col-md-3">
                                    <select id="buku-lagu-{{ $item->id }}" class="form-select bg-light">
                                        <option value="KJ">KJ</option><option value="NKB">NKB</option><option value="PKJ">PKJ</option><option value="NR">NR</option><option value="DSL">DSL</option><option value="GPM">GPM</option><option value="BEBAS">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" id="nomor-lagu-{{ $item->id }}" class="form-control bg-light" placeholder="No. Lagu">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-primary w-100" data-song-picker title="Pilih dari lagu yang sudah ada di database">Pilih</button>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-secondary w-100" data-song-pull onclick="tarikLagu({{ $item->id }}, event)">Tarik</button>
                                </div>
                            </div>

                            <input type="text" name="dynamic_content[{{ $item->id }}][judul]" class="form-control mb-3 fw-bold text-primary" placeholder="Judul Lagu" value="{{ is_array($val) ? ($val['judul'] ?? '') : '' }}">
                            
                            <div id="bait-container-{{ $item->id }}">
                                @if(is_array($val) && isset($val['bait']) && is_array($val['bait']))
                                    @foreach($val['bait'] as $key => $baitText)
                                        @php 
                                            $isReffKey = (is_string($key) && stripos($key, 'ref') !== false) || preg_match('/^\[?reff?\]?[\s\:\.\-]?/i', $baitText);
                                            $displayKey = $isReffKey ? 'Reff' : preg_replace('/[^0-9]/', '', $key);
                                            if(empty($displayKey) && !$isReffKey) $displayKey = $key;
                                            
                                            $cleanTextForEdit = preg_replace('/^\[?REFF\]?\s*/i', '', $baitText);
                                            $bgClass = $isReffKey ? 'bg-warning text-dark' : 'bg-light text-secondary';
                                        @endphp
                                        <div class="input-group mb-2 shadow-sm position-relative bait-item">
                                            <div class="input-group-text {{ $bgClass }} p-0 overflow-hidden" style="width: 80px;">
                                                <input type="text" class="bait-label-input {{ $bgClass }}" onchange="updateBaitName(this, '{{ $item->id }}')" value="{{ $displayKey }}">
                                            </div>
                                            <input type="hidden" class="bait-hidden-key" name="dynamic_content[{{ $item->id }}][bait][{{ $key }}]" value="{{ $isReffKey ? '[REFF] ' . trim($cleanTextForEdit) : trim($cleanTextForEdit) }}">
                                            <textarea class="form-control" rows="3" placeholder="Ketik lirik..." oninput="this.previousElementSibling.value = this.value">{{ $isReffKey ? '[REFF] ' . trim($cleanTextForEdit) : trim($cleanTextForEdit) }}</textarea>
                                            <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.closest('.bait-item').remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2 shadow-sm position-relative bait-item">
                                        <div class="input-group-text bg-light text-secondary p-0 overflow-hidden" style="width: 80px;">
                                            <input type="text" class="bait-label-input" onchange="updateBaitName(this, '{{ $item->id }}')" value="1">
                                        </div>
                                        <input type="hidden" class="bait-hidden-key" name="dynamic_content[{{ $item->id }}][bait][1]" value="">
                                        <textarea class="form-control" rows="3" placeholder="Ketik lirik..." oninput="this.previousElementSibling.value = this.value"></textarea>
                                        <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.closest('.bait-item').remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="tambahBait({{ $item->id }})">&plus; Tambah Bait Lirik</button>
                            
                        @elseif($type === 'alkitab')
                            <div class="input-group mb-2">
                                <input type="text" id="input-alkitab-{{ $item->id }}" name="dynamic_content[{{ $item->id }}][ref]" class="form-control bg-light" placeholder="Cth: Yoh 3:16-18 · Mzm 23 · Yes 45:1-8" value="{{ is_array($val) ? ($val['ref'] ?? '') : '' }}" onkeydown="if(event.key==='Enter'){event.preventDefault();this.nextElementSibling.click();}">
                                <button type="button" class="btn btn-secondary px-4" onclick="tarikAlkitab({{ $item->id }}, event)">Tarik</button>
                            </div>
                                                        <div class="form-text mb-2">Alkitab Terjemahan Baru (TB). Kosongkan teks ayat bila layar cukup menampilkan referensinya saja.</div>
                            <textarea id="textarea-{{ $item->id }}" name="dynamic_content[{{ $item->id }}][content]" class="form-control" rows="5" placeholder="16 Karena begitu besar kasih Allah ...">{{ is_array($val) ? ($val['content'] ?? '') : $val }}</textarea>

                        @else
                            @php
                                $customTitle = is_array($val) ? ($val['custom_title'] ?? '') : $cleanTitle;
                                $textContent = is_array($val) ? ($val['content'] ?? '') : $val;
                            @endphp
                            <label class="form-label small fw-bold text-secondary">Judul Sesi (Opsional)</label>
                            <input type="text" name="dynamic_content[{{ $item->id }}][custom_title]" class="form-control mb-2 fw-bold text-dark" placeholder="Kosongkan jika ingin teks di tengah layar" value="{{ $customTitle }}">
                            
                            <label class="form-label small fw-bold text-secondary">Isi Teks</label>
                            <textarea id="textarea-{{ $item->id }}" name="dynamic_content[{{ $item->id }}][content]" class="form-control" rows="3" placeholder="Ketik pengumuman atau instruksi...">{{ $textContent }}</textarea>
                        @endif
                        
                        <div class="form-check form-switch mt-3 pt-3 border-top">
                            <input class="form-check-input" type="checkbox" name="dynamic_content[{{ $item->id }}][use_camera]" value="true" id="cam-{{ $item->id }}" {{ $useCamera ? 'checked' : '' }}>
                            <label class="form-check-label small fw-bold text-muted" for="cam-{{ $item->id }}">
                                Sorot Pengisi Acara (Aktifkan Latar Kamera & Teks Bawah)
                            </label>
                        </div>

                    @else
                        <textarea name="dynamic_content[{{ $item->id }}]" class="form-control text-secondary bg-light" rows="2" readonly>{{ $item->static_content }}</textarea>
                    @endif

                    <div class="mt-4 pt-3 border-top" style="border-color: #edf2f7 !important;">
                        <button type="button" class="btn btn-sm btn-light border fw-medium px-4 rounded" onclick="tambahSlideKhusus({{ $item->id }})">
                            &plus; Sisipkan Slide Tambahan (Kustom)
                        </button>
                        <div id="custom-slide-container-{{ $item->id }}" class="mt-2">
                            @if(isset($schedule->customSlides) && $schedule->customSlides->where('liturgy_item_id', $item->id)->count() > 0)
                                @foreach($schedule->customSlides->where('liturgy_item_id', $item->id) as $cSlide)
                                    @if($cSlide->isSong())
                                        <div class="p-3 mb-2 border rounded bg-light position-relative custom-slide-box">
                                            <button type="button" class="btn-delete-block" onclick="this.closest('.custom-slide-box').remove()" style="top:5px; right:5px;" title="Hapus lagu tambahan">&times;</button>
                                            <input type="hidden" name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][kind]" value="song">
                                            <input type="hidden" name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][title]" value="{{ $cSlide->title }}">
                                            <input type="hidden" name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][content]" value="{{ $cSlide->content }}">
                                            <span class="badge text-bg-warning me-1">LAGU TAMBAHAN</span> <b>{{ $cSlide->songData()['judul'] }}</b>
                                        </div>
                                        @continue
                                    @endif
                                    @php
                                        $isCameraCustom = is_string($cSlide->content) && str_contains($cSlide->content, '[KAMERA_AKTIF]');
                                        $cleanCustomContent = str_replace('[KAMERA_AKTIF]', '', $cSlide->content);
                                    @endphp
                                    <div class="p-3 mb-2 border rounded bg-light position-relative custom-slide-box">
                                        <button type="button" class="btn-delete-block" onclick="this.closest('.custom-slide-box').remove()" style="top:5px; right:5px;">&times;</button>
                                        <div class="mb-2 pe-4">
                                            <input type="text" name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][title]" class="form-control fw-medium" placeholder="Judul Slide Sisipan" value="{{ cleanSlideTitle($cSlide->title) }}">
                                        </div>
                                        <textarea name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][content]" class="form-control" rows="3" placeholder="Isi konten slide...">{{ $cleanCustomContent }}</textarea>
                                        
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][use_camera]" value="true" id="cam-custom-{{ $cSlide->id }}" {{ $isCameraCustom ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-bold text-muted" for="cam-custom-{{ $cSlide->id }}">Latar kamera (teks di bawah)</label>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="toolbar-menu">
                <div class="container d-flex justify-content-end">
                    <button type="submit" form="editForm" class="btn btn-primary btn-primary-custom">Simpan perubahan</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
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
                // Gunakan ID unik untuk formKey jika Reff, agar tidak tertimpa
                let formKey = newName;
                if(newName.toLowerCase() === 'reff') {
                    let existingKey = hiddenInput.name.match(/\[bait\]\[(.*?)\]/);
                    if (existingKey && existingKey[1].toLowerCase().includes('ref')) {
                        formKey = existingKey[1]; 
                    } else {
                        formKey = 'ref_' + Math.random().toString(36).substr(2, 5); 
                    }
                }
                hiddenInput.name = `dynamic_content[${itemId}][bait][${formKey}]`;
            }
        }

        function tambahBait(itemId) {
            const containerId = 'bait-container-' + itemId;
            const baitNum = Date.now().toString().slice(-4);
            
            const html = `
                <div class="input-group mb-2 shadow-sm position-relative bait-item">
                    <div class="input-group-text bg-light text-secondary p-0 overflow-hidden" style="width: 80px;">
                        <input type="text" class="bait-label-input bg-light text-secondary" onchange="updateBaitName(this, '${itemId}')" value="${baitNum}" placeholder="Angka">
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
                <div class="p-3 mb-2 border rounded bg-light position-relative custom-slide-box">
                    <button type="button" class="btn-delete-block" onclick="this.closest('.custom-slide-box').remove()" style="top:5px; right:5px;">&times;</button>
                    <div class="mb-2 pe-4">
                        <input type="text" name="custom_slides[${itemId}][${slideId}][title]" class="form-control fw-medium" placeholder="Judul Slide Sisipan (Kosongkan jika teks di tengah)" >
                    </div>
                    <textarea name="custom_slides[${itemId}][${slideId}][content]" class="form-control" rows="3" placeholder="Isi konten slide..." required></textarea>
                    
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="custom_slides[${itemId}][${slideId}][use_camera]" value="true" id="cam-custom-${slideId}">
                        <label class="form-check-label small fw-bold text-muted" for="cam-custom-${slideId}">Latar kamera (teks di bawah)</label>
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
                                
                                let labelText = isReff ? 'Reff' : ((data.labels && data.labels[idx] && data.labels[idx] !== 'Reff') ? data.labels[idx] : verseCounter);
                                let bgClass = isReff ? 'bg-warning text-dark' : 'bg-light text-secondary';
                                let formKey = isReff ? 'ref_' + idx : labelText;
                                
                                const html = `
                                    <div class="input-group mb-2 shadow-sm position-relative bait-item">
                                        <div class="input-group-text ${bgClass} p-0 overflow-hidden" style="width: 80px;">
                                            <input type="text" class="bait-label-input ${bgClass}" onchange="updateBaitName(this, '${itemId}')" value="${labelText}">
                                        </div>
                                        <input type="hidden" class="bait-hidden-key" name="dynamic_content[${itemId}][bait][${formKey}]" value="${escHtml(isReff ? '[REFF]\n' + cleanBait : cleanBait)}">
                                        <textarea class="form-control" rows="3" oninput="this.previousElementSibling.value = this.value">${escHtml(isReff ? '[REFF]\n' + cleanBait : cleanBait)}</textarea>
                                        <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.closest('.bait-item').remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                                    </div>`;
                                container.insertAdjacentHTML('beforeend', html);
                                
                                if(!isReff) verseCounter++;
                            }
                        });
                    } else { alert(data.message); }
                }).catch(err => alert('Gagal menarik data lagu.')).finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
        }

        // Lirik dimasukkan ke HTML: tanda kutip/< > harus di-escape agar lirik tidak terpotong
        function escHtml(s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }

        function tarikAlkitab(itemId, event) {
            const inputField = document.getElementById('input-alkitab-' + itemId);
            const textarea = document.getElementById('textarea-' + itemId);
            const btn = event.currentTarget;
            const query = inputField.value.trim();
            
            if(!query) { alert('Tulis nama kitab dan pasalnya.'); return; }
            
            const originalText = btn.innerHTML;
            btn.innerHTML = '...'; btn.disabled = true;
            
            const before = textarea.value;
            fetch(`/api/fetch-alkitab?q=${encodeURIComponent(query)}`, { headers: { Accept: 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        inputField.value = data.reference;
                        textarea.value = data.text;
                    } else { textarea.value = before; alert(data.message); }
                })
                .catch(err => alert('Terjadi kesalahan koneksi.'))
                .finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
        }
    </script>
@endpush
