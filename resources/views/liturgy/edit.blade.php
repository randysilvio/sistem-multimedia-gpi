<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tata Ibadah - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; color: #1e293b; padding-bottom: 50px; }
        .top-banner { height: 120px; background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); border-radius: 0 0 15px 15px; }
        .form-container { max-width: 800px; margin: -60px auto 0; padding: 0 15px; }
        .card-form { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .card-form-header { border-top: 8px solid #3b82f6; }
        .form-label { font-weight: 600; color: #475569; font-size: 0.9rem; margin-bottom: 8px; }
        .form-control, .form-select { border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 15px; font-size: 0.95rem; background-color: #f8fafc; transition: all 0.2s; }
        .form-control:focus, .form-select:focus { border-color: #3b82f6; background-color: #ffffff; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .btn-add-slide { font-size: 0.85rem; font-weight: 600; border: 2px dashed #cbd5e1; color: #64748b; background: transparent; padding: 10px; width: 100%; border-radius: 8px; transition: 0.2s; }
        .btn-add-slide:hover { border-color: #3b82f6; color: #3b82f6; background: #eff6ff; }
        .custom-slide-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-top: 15px; border-left: 4px solid #3b82f6; }
        .header-title { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-bottom: 5px; }
    </style>
</head>
<body>

    <div class="top-banner"></div>

    <div class="form-container">
        <form action="{{ route('liturgy.update', $schedule->id) }}" method="POST">
            @csrf
            
            <div class="card-form card-form-header">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h1 class="header-title">Edit: {{ $schedule->liturgy->name ?? 'Tata Ibadah' }}</h1>
                        <p class="text-secondary small mb-0">Tanggal: {{ \Carbon\Carbon::parse($schedule->worship_date)->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <a href="{{ route('liturgy.gallery') }}" class="btn btn-light border text-secondary fw-medium px-4">Kembali</a>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tema / Nama Ibadah</label>
                        <input type="text" name="theme" class="form-control" value="{{ $schedule->theme }}" placeholder="Contoh: Ibadah Minggu Pagi">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pelayan Firman</label>
                        <input type="text" name="preacher_name" class="form-control" value="{{ $schedule->preacher_name }}" placeholder="Nama Pendeta/Pelayan">
                    </div>
                </div>
            </div>

            @foreach($schedule->liturgy->items as $item)
                @php 
                    $detail = $schedule->details->where('liturgy_item_id', $item->id)->first();
                    $val = $detail ? $detail->dynamic_content : ($item->static_content ?? '');
                @endphp
                
                <div class="card-form">
                    <label class="form-label text-primary fs-5">{{ $item->title }}</label>
                    <hr class="mt-1 mb-3 text-muted">
                    
                    @if($item->is_dynamic)

                        @if(str_contains(strtolower($item->title), 'sikap') || str_contains(strtolower($item->title), 'aksi'))
                            @php $sikapVal = is_array($val) ? ($val['content'] ?? $val[0] ?? '') : $val; @endphp
                            <select name="dynamic_content[{{ $item->id }}]" class="form-select">
                                <option value="(Jemaat Berdiri)" {{ str_contains($sikapVal, 'Berdiri') ? 'selected' : '' }}>(Jemaat Berdiri)</option>
                                <option value="(Jemaat Duduk)" {{ str_contains($sikapVal, 'Duduk') && !str_contains($sikapVal, 'Teduh') ? 'selected' : '' }}>(Jemaat Duduk)</option>
                                <option value="(Saat Teduh)" {{ str_contains($sikapVal, 'Teduh') ? 'selected' : '' }}>(Saat Teduh / Lilin Dipadamkan)</option>
                                <option value="(Jemaat Duduk - Calon Sidi Berlutut)" {{ str_contains($sikapVal, 'Berlutut') ? 'selected' : '' }}>(Jemaat Duduk - Calon Sidi Berlutut)</option>
                            </select>

                        @elseif(str_contains(strtolower($item->title), 'pra-ibadah') || str_contains(strtolower($item->title), 'prosesi'))
                            <input type="text" name="dynamic_content[{{ $item->id }}][custom_title]" class="form-control mb-2 fw-medium" value="{{ is_array($val) ? ($val['custom_title'] ?? '') : str_replace(' (Opsional)', '', $item->title) }}" placeholder="Judul Tampilan">
                            <textarea name="dynamic_content[{{ $item->id }}][content]" class="form-control" rows="3">{{ is_array($val) ? ($val['content'] ?? '') : (is_string($val) ? $val : '') }}</textarea>
                            
                        @elseif(str_contains(strtolower($item->title), 'nyanyian') || str_contains(strtolower($item->title), 'pujian'))
                            <div class="row g-2 mb-2">
                                <div class="col-md-3">
                                    <select id="buku-lagu-{{ $item->id }}" class="form-select">
                                        <option value="KJ">KJ</option>
                                        <option value="NKB">NKB</option>
                                        <option value="PKJ">PKJ</option>
                                        <option value="NR">NR</option>
                                        <option value="BEBAS">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <input type="text" id="nomor-lagu-{{ $item->id }}" class="form-control" placeholder="No. Lagu">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-dark w-100" onclick="tarikLagu({{ $item->id }}, event)">Tarik</button>
                                </div>
                            </div>

                            <input type="text" name="dynamic_content[{{ $item->id }}][judul]" class="form-control mb-3 fw-bold" placeholder="Judul Lagu" value="{{ is_array($val) ? ($val['judul'] ?? '') : '' }}">
                            
                            <div id="bait-container-{{ $item->id }}">
                                @if(is_array($val) && isset($val['bait']) && is_array($val['bait']))
                                    @foreach($val['bait'] as $idx => $bait)
                                        <div class="position-relative mb-2 border rounded p-2 bg-light">
                                            <span class="badge bg-secondary mb-1">Bait {{ $idx }}</span>
                                            <textarea name="dynamic_content[{{ $item->id }}][bait][{{ $idx }}]" class="form-control" rows="3">{{ $bait }}</textarea>
                                            <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1" onclick="this.parentElement.remove()">&times;</button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn-add-slide mt-2" onclick="tambahBait({{ $item->id }})">&plus; Tambah Bait Lirik</button>
                            
                        @else
                            @if(str_contains(strtolower($item->title), 'alkitab') || str_contains(strtolower($item->title), 'bacaan'))
                                <div class="row g-2 mb-2">
                                    <div class="col-md-10">
                                        <input type="text" id="input-alkitab-{{ $item->id }}" class="form-control" placeholder="Cari Kitab (Cth: Yohanes 3:16)">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-dark w-100" onclick="tarikAlkitab({{ $item->id }}, event)">Tarik</button>
                                    </div>
                                </div>
                            @endif
                            <textarea id="textarea-{{ $item->id }}" name="dynamic_content[{{ $item->id }}]" class="form-control" rows="4">{{ is_array($val) ? ($val['content'] ?? '') : $val }}</textarea>
                        @endif
                        
                    @else
                        <textarea name="dynamic_content[{{ $item->id }}]" class="form-control text-secondary bg-light" rows="2" readonly>{{ $item->static_content }}</textarea>
                    @endif

                    <div class="mt-4 pt-3 border-top">
                        <label class="form-label text-secondary"><small>Slide Tambahan / Sisipan (Opsional)</small></label>
                        <div id="custom-slide-container-{{ $item->id }}">
                            @if(isset($schedule->customSlides) && $schedule->customSlides->where('liturgy_item_id', $item->id)->count() > 0)
                                @foreach($schedule->customSlides->where('liturgy_item_id', $item->id) as $index => $cSlide)
                                    <div class="custom-slide-box position-relative">
                                        <input type="text" name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][title]" class="form-control mb-2 fw-medium" value="{{ $cSlide->title }}">
                                        <textarea name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][content]" class="form-control" rows="3">{{ $cSlide->content }}</textarea>
                                        <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()">Hapus</button>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" class="btn-add-slide mt-2" onclick="tambahSlideKhusus({{ $item->id }})">&plus; Sisipkan Slide Tambahan di Bagian Ini</button>
                    </div>
                </div>
            @endforeach
            
            <div class="text-center mt-4 mb-5">
                <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow">SIMPAN DATA IBADAH</button>
            </div>
        </form>
    </div>

<script>
    function tambahBait(itemId) {
        const container = document.getElementById('bait-container-' + itemId);
        let maxNum = 0;
        container.querySelectorAll('textarea').forEach(ta => {
            const match = ta.name.match(/\[bait\]\[(\d+)\]/);
            if(match && parseInt(match[1]) > maxNum) maxNum = parseInt(match[1]);
        });
        const nextNum = maxNum + 1;
        const html = `<div class="position-relative mb-2 border rounded p-2 bg-light"><span class="badge bg-secondary mb-1">Bait ${nextNum}</span><textarea name="dynamic_content[${itemId}][bait][${nextNum}]" class="form-control" rows="3"></textarea><button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1" onclick="this.parentElement.remove()">&times;</button></div>`;
        container.insertAdjacentHTML('beforeend', html);
    }

    function tambahSlideKhusus(itemId) {
        const container = document.getElementById('custom-slide-container-' + itemId); 
        const slideId = Math.random().toString(36).substr(2, 9);
        const html = `<div class="custom-slide-box position-relative"><input type="text" name="custom_slides[${itemId}][${slideId}][title]" class="form-control mb-2 fw-medium" placeholder="Judul Sisipan"><textarea name="custom_slides[${itemId}][${slideId}][content]" class="form-control" rows="3" placeholder="Isi teks slide..."></textarea><button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()">Hapus</button></div>`;
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
                    let verseNum = 1;
                    baits.forEach(bait => {
                        if(bait.trim() !== '') {
                            const html = `<div class="position-relative mb-2 border rounded p-2 bg-light"><span class="badge bg-secondary mb-1">Bait ${verseNum}</span><textarea name="dynamic_content[${itemId}][bait][${verseNum}]" class="form-control" rows="3">${bait.trim()}</textarea><button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1" onclick="this.parentElement.remove()">&times;</button></div>`;
                            container.insertAdjacentHTML('beforeend', html);
                            verseNum++;
                        }
                    });
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