<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Control Panel - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; color: #1e293b; overflow: hidden; }
        .navbar { background-color: #0f172a !important; }
        .form-control:focus, .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25); }
        textarea { resize: vertical; }

        .left-panel { height: calc(100vh - 65px); overflow-y: auto; padding: 30px; background-color: #f8fafc; }
        .card-edit { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .form-label-header { font-weight: 700; color: #334155; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 15px; }
        
        .right-panel { height: calc(100vh - 65px); overflow-y: auto; background-color: #1e293b; padding: 25px; border-left: 2px solid #334155; position: relative; padding-bottom: 90px; }
        .preview-header { position: sticky; top: -25px; background: #1e293b; z-index: 10; padding-bottom: 15px; padding-top: 10px; border-bottom: 1px solid #334155; margin-bottom: 20px; }
        
        .preview-card { background-color: #334155; color: #f8fafc; border-radius: 8px; padding: 15px; height: 140px; cursor: pointer; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; border: 2px solid transparent; transition: all 0.2s ease; user-select: none; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
        .preview-card:hover { border-color: #60a5fa; transform: translateY(-2px); }
        .preview-card.active { border-color: #fcd34d; background-color: #0f172a; box-shadow: 0 0 15px rgba(252, 211, 77, 0.3); }
        .preview-title { font-size: 0.65rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; font-weight: 700; }
        .preview-content { font-size: 0.85rem; font-weight: 700; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; }
        
        /* FLOATING FONT CONTROLLER */
        .font-controller { position: fixed; bottom: 20px; right: 30px; background: #0f172a; border: 1px solid #334155; border-radius: 50px; padding: 10px 20px; display: flex; align-items: center; gap: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); z-index: 100; transition: 0.3s; opacity: 0; pointer-events: none; transform: translateY(20px);}
        .font-controller.show { opacity: 1; pointer-events: auto; transform: translateY(0); }
        .btn-font { background: #334155; color: white; border: none; width: 35px; height: 35px; border-radius: 50%; font-weight: bold; display: flex; align-items: center; justify-content: center; transition: 0.2s; cursor: pointer; }
        .btn-font:hover { background: #60a5fa; color: #0f172a; }
        .btn-font-auto { background: transparent; border: 1px solid #64748b; color: #cbd5e1; border-radius: 20px; padding: 0 15px; font-size: 0.8rem; height: 35px; transition: 0.2s; cursor: pointer; font-weight: 600;}
        .btn-font-auto:hover { background: #334155; color: white; }
        .font-indicator { color: #fcd34d; font-weight: 700; font-size: 0.9rem; min-width: 45px; text-align: center; }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .right-panel::-webkit-scrollbar-thumb { background: #475569; }
    </style>
</head>
<body>

    @php
    if (!function_exists('autoSplitText')) {
        function autoSplitText($text) {
            if (is_array($text)) { $text = $text['content'] ?? ''; }
            if (!is_string($text)) $text = '';
            if (str_contains($text, '===SLIDE_BREAK===')) return array_filter(array_map('trim', explode('===SLIDE_BREAK===', $text)));
            
            $text = preg_replace("/[\r\n]+/", "\n", trim($text));
            $paragraphs = explode("\n", $text);
            $slides = []; $currentSlide = [];
            $maxCharsPerLine = 35; 

            foreach ($paragraphs as $para) {
                $para = trim($para); if (empty($para)) continue;
                $words = explode(' ', $para); $currentLine = '';
                foreach ($words as $word) {
                    if (strlen($currentLine) + strlen($word) + 1 > $maxCharsPerLine && !empty($currentLine)) {
                        $currentSlide[] = trim($currentLine);
                        $currentLine = $word;
                        if (count($currentSlide) >= 3) {
                            $slides[] = implode("\n", $currentSlide);
                            $currentSlide = [];
                        }
                    } else { $currentLine = empty($currentLine) ? $word : $currentLine . ' ' . $word; }
                }
                if (!empty($currentLine)) {
                    $currentSlide[] = trim($currentLine);
                    if (count($currentSlide) >= 3) {
                        $slides[] = implode("\n", $currentSlide);
                        $currentSlide = [];
                    }
                }
            }
            if (!empty($currentSlide)) { $slides[] = implode("\n", $currentSlide); }
            
            $finalSlides = []; $total = count($slides);
            for ($i = 0; $i < $total; $i++) {
                if ($i === $total - 1 && $total > 1) {
                    $wordCount = str_word_count(strip_tags(str_replace("\n", " ", $slides[$i])));
                    if ($wordCount <= 4) { 
                        $lastIndex = count($finalSlides) - 1;
                        $finalSlides[$lastIndex] .= ' ' . str_replace("\n", " ", trim($slides[$i]));
                        continue; 
                    }
                }
                $finalSlides[] = $slides[$i];
            }
            return empty($finalSlides) ? [$text] : $finalSlides;
        }
    }

    $allSlides = [];
    $allSlides[] = [
        'judul' => strtoupper($schedule->liturgy->name ?? $schedule->theme ?? 'IBADAH'),
        'isi' => \Carbon\Carbon::parse($schedule->worship_date)->translatedFormat('l, d F Y') . ($schedule->theme ? "\n" . strtoupper($schedule->theme) : "")
    ];

    foreach($liturgyItems as $item) {
        $detail = $scheduleDetails->get($item->id);
        $content = $detail ? $detail->dynamic_content : $item->static_content;
        $isEmptyArray = is_array($content) && empty($content['judul']) && empty($content['bait']) && empty($content['content']);
        $isEmptyString = !is_array($content) && trim($content) === '';
        $isInstruksi = str_contains(strtolower($item->title), 'sikap') || str_contains(strtolower($item->title), 'aksi');

        if($content && !$isEmptyArray && !$isEmptyString) {
            if($isInstruksi) {
                $textInstruksi = is_array($content) ? ($content['content'] ?? $content[0] ?? '') : $content;
                $allSlides[] = ['judul' => 'INSTRUKSI', 'isi' => $textInstruksi];
            } elseif(is_array($content)) {
                if(isset($content['custom_title'])) {
                    $slidesText = autoSplitText($content['content'] ?? '');
                    foreach($slidesText as $st) {
                        $allSlides[] = ['judul' => $content['custom_title'], 'isi' => $st];
                    }
                } elseif(!empty($content['bait'])) {
                    $allSlides[] = ['judul' => str_replace(' (Opsional)', '', $item->title), 'isi' => ($content['judul'] ?? '')];
                    foreach($content['bait'] as $index => $bait) {
                        $baitSlides = autoSplitText($bait);
                        foreach($baitSlides as $bSlide) {
                            $allSlides[] = ['judul' => str_replace(' (Opsional)', '', $item->title) . ' - Bait ' . $index, 'isi' => $bSlide];
                        }
                    }
                }
            } else {
                $slidesText = autoSplitText($content);
                foreach($slidesText as $st) {
                    $allSlides[] = ['judul' => str_replace(' (Opsional)', '', $item->title), 'isi' => $st];
                }
            }
        }
        if(isset($customSlides[$item->id])) {
            foreach($customSlides[$item->id] as $cSlide) {
                $cSlidesText = autoSplitText($cSlide->content);
                foreach($cSlidesText as $ct) {
                    $allSlides[] = ['judul' => $cSlide->title, 'isi' => $ct];
                }
            }
        }
    }
    $allSlides[] = ['judul' => 'PENUTUP', 'isi' => "TUHAN YESUS\nMEMBERKATI"];
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm py-2">
        <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-uppercase m-0 d-flex align-items-center" href="{{ route('liturgy.gallery') }}">
                <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" alt="Logo GPI" height="28" class="me-3">
                <span class="fw-bold" style="font-size: 0.9rem; letter-spacing: 1px;">Sistem Multimedia</span>
            </a>
            <div>
                <button type="submit" form="editForm" class="btn btn-success btn-sm fw-bold px-4 me-2">SIMPAN PERUBAHAN</button>
                <a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-light btn-sm fw-medium px-3">Tutup</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-0">
        <div class="row g-0">
            
            <div class="col-lg-6 left-panel">
                <h5 class="fw-bold text-dark mb-1">Edit: {{ $schedule->liturgy->name ?? $schedule->theme }}</h5>
                <p class="text-secondary small mb-4">Pastikan Anda menyimpan perubahan sebelum membuka proyektor.</p>

                @if ($errors->any())
                    <div class="alert alert-danger shadow-sm border-0 border-start border-4 border-danger mb-4">
                        <ul class="mb-0 mt-1 small">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif
                @if(session('success')) 
                    <div class="alert alert-success shadow-sm border-0 border-start border-4 border-success mb-4 small">{{ session('success') }}</div> 
                @endif

                <form action="{{ route('liturgy.update', $schedule->id) }}" method="POST" id="editForm">
                    @csrf @method('PUT')
                    
                    <div class="card-edit bg-white">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="small text-secondary fw-bold mb-1">Tanggal</label>
                                <input type="date" name="worship_date" class="form-control" value="{{ $schedule->worship_date }}" required>
                            </div>
                            <div class="col-md-8">
                                <label class="small text-secondary fw-bold mb-1">Tema / Sub Tema</label>
                                <input type="text" name="theme" class="form-control" value="{{ $schedule->theme }}">
                            </div>
                            <div class="col-md-9">
                                <label class="small text-secondary fw-bold mb-1">Pelayan Firman</label>
                                <input type="text" name="preacher_name" class="form-control" value="{{ $schedule->preacher_name }}">
                            </div>
                            <div class="col-md-3">
                                <label class="small text-secondary fw-bold mb-1">Warna Latar</label>
                                <input type="color" name="theme_color" class="form-control form-control-color w-100 p-1" value="{{ $schedule->theme_color ?? '#1b2735' }}">
                            </div>
                        </div>
                    </div>

                    @foreach($liturgyItems as $item)
                        @php 
                            $isOptional = str_contains(strtolower($item->title), 'opsional'); 
                            $reqRule = $isOptional ? '' : 'required';
                            $detail = $scheduleDetails->get($item->id);
                            $val = $detail ? $detail->dynamic_content : $item->static_content;
                        @endphp

                        <div class="card-edit bg-white position-relative">
                            <label class="form-label-header d-flex justify-content-between align-items-center">
                                <span>{{ $item->title }}</span>
                                @if($isOptional) <span class="badge bg-light text-secondary border fw-normal" style="text-transform: none;">Opsional</span> @endif
                            </label>
                            
                            @if($item->is_dynamic)
                                @if(stripos(strtolower($item->title), 'sikap') !== false)
                                    @php $sikapVal = is_array($val) ? ($val['content'] ?? $val[0] ?? '') : $val; @endphp
                                    <select name="dynamic_content[{{ $item->id }}]" class="form-select fw-medium bg-light">
                                        <option value="(Jemaat Berdiri)" {{ strpos($sikapVal, 'Berdiri') !== false ? 'selected' : '' }}>(Jemaat Berdiri)</option>
                                        <option value="(Jemaat Duduk)" {{ strpos($sikapVal, 'Duduk') !== false ? 'selected' : '' }}>(Jemaat Duduk)</option>
                                        <option value="(Saat Teduh)" {{ strpos($sikapVal, 'Teduh') !== false ? 'selected' : '' }}>(Saat Teduh / Lilin Dipadamkan)</option>
                                    </select>
                                @elseif(str_contains(strtolower($item->title), 'pra-ibadah') || str_contains(strtolower($item->title), 'prosesi'))
                                    @php 
                                        $ctitle = is_array($val) ? ($val['custom_title'] ?? '') : str_replace(' (Opsional)', '', $item->title);
                                        $cisi = is_array($val) ? ($val['content'] ?? '') : $val;
                                    @endphp
                                    <input type="text" name="dynamic_content[{{ $item->id }}][custom_title]" class="form-control mb-2 fw-medium text-dark" value="{{ $ctitle }}">
                                    <textarea name="dynamic_content[{{ $item->id }}][content]" class="form-control" rows="6" {{ $reqRule }}>{{ $cisi }}</textarea>
                                @elseif(stripos(strtolower($item->title), 'nyanyian') !== false || stripos(strtolower($item->title), 'pujian') !== false || stripos(strtolower($item->title), 'gloria') !== false || stripos(strtolower($item->title), 'introitus') !== false)
                                    @php
                                        $judulLagu = is_array($val) ? ($val['judul'] ?? '') : '';
                                        $baits = is_array($val) ? ($val['bait'] ?? []) : [];
                                    @endphp
                                    <div class="input-group mb-2">
                                        <select id="buku-lagu-{{ $item->id }}" class="form-select bg-light" style="max-width: 100px;">
                                            <option value="KJ">KJ</option><option value="NKB">NKB</option><option value="PKJ">PKJ</option><option value="NR">NR</option><option value="BEBAS">Lainnya</option>
                                        </select>
                                        <input type="text" id="nomor-lagu-{{ $item->id }}" class="form-control bg-light" placeholder="No. (Cth: 15)">
                                        <button type="button" class="btn btn-secondary fw-medium px-3" onclick="tarikLagu({{ $item->id }}, event)">Tarik Lirik</button>
                                    </div>
                                    <input type="text" name="dynamic_content[{{ $item->id }}][judul]" class="form-control mb-2 fw-medium text-primary" placeholder="Judul Lagu Manual / Otomatis" value="{{ $judulLagu }}">
                                    <div id="bait-container-{{ $item->id }}">
                                        @if(!empty($baits))
                                            @foreach($baits as $index => $baitText)
                                            <div class="input-group mb-2 shadow-sm position-relative">
                                                <span class="input-group-text bg-light text-secondary" style="font-size:0.8rem; min-width: 70px;">Bait {{ $index }}</span>
                                                <textarea name="dynamic_content[{{ $item->id }}][bait][{{ $index }}]" class="form-control" rows="3">{{ $baitText }}</textarea>
                                                <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.parentElement.remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                                            </div>
                                            @endforeach
                                        @else
                                            <div class="input-group mb-2 shadow-sm position-relative">
                                                <span class="input-group-text bg-light text-secondary" style="font-size:0.8rem; min-width: 70px;">Bait 1</span>
                                                <textarea name="dynamic_content[{{ $item->id }}][bait][1]" class="form-control" rows="3" placeholder="Ketik lirik secara manual..."></textarea>
                                            </div>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light border w-100 fw-medium text-secondary mt-1" onclick="tambahBait({{ $item->id }})">&plus; Tambah Bait Lirik Manual</button>
                                @elseif(stripos(strtolower($item->title), 'alkitab') !== false || stripos(strtolower($item->title), 'bacaan') !== false)
                                    <div class="input-group mb-2">
                                        <input type="text" id="input-alkitab-{{ $item->id }}" class="form-control bg-light" placeholder="Cari Kitab (Contoh: Yohanes 3:16)">
                                        <button type="button" class="btn btn-secondary fw-medium btn-sm px-4" onclick="tarikAlkitab({{ $item->id }}, event)">Tarik Teks Alkitab</button>
                                    </div>
                                    <textarea id="textarea-{{ $item->id }}" name="dynamic_content[{{ $item->id }}]" class="form-control" rows="6" {{ $reqRule }}>{{ is_array($val) ? '' : $val }}</textarea>
                                @else
                                    <textarea id="textarea-{{ $item->id }}" name="dynamic_content[{{ $item->id }}]" class="form-control {{ str_contains(strtolower($item->title), 'sikap') || str_contains(strtolower($item->title), 'aksi') ? 'bg-light text-secondary fw-bold' : '' }}" rows="6" {{ $reqRule }}>{{ is_array($val) ? '' : $val }}</textarea>
                                @endif
                            @else
                                <textarea name="dynamic_content[{{ $item->id }}]" class="form-control text-secondary bg-light" rows="3" readonly>{{ $val }}</textarea>
                            @endif

                            <div class="mt-4 pt-3 border-top" style="border-color: #edf2f7 !important;">
                                <button type="button" class="btn btn-sm btn-outline-primary fw-medium px-4 rounded-pill" onclick="tambahSlideKhusus({{ $item->id }})">&plus; Sisipkan Slide Tambahan Di Sini</button>
                                <div id="custom-slide-container-{{ $item->id }}" class="mt-3">
                                    @if(isset($customSlides[$item->id]))
                                        @foreach($customSlides[$item->id] as $cSlide)
                                            <div class="p-3 mb-3 border border-2 rounded bg-light position-relative" style="border-color: #cbd5e0 !important;">
                                                <span class="badge bg-secondary mb-2">Slide Sisipan Tersimpan</span>
                                                <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()"></button>
                                                <div class="mb-2">
                                                    <input type="text" name="custom_slides[{{ $item->id }}][existing_{{ $cSlide->id }}][title]" class="form-control fw-medium border-secondary" value="{{ $cSlide->title }}" required>
                                                </div>
                                                <div>
                                                    <textarea name="custom_slides[{{ $item->id }}][existing_{{ $cSlide->id }}][content]" class="form-control border-secondary" rows="4" required>{{ $cSlide->content }}</textarea>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </form>
            </div>

            <div class="col-lg-6 right-panel">
                <div class="preview-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-white fw-bold mb-0">Control Panel / Preview</h5>
                        <p class="text-secondary small mb-0" style="font-size: 0.75rem;">
                            <strong>Klik 1x</strong> untuk Lompat. <strong>Klik 2x</strong> untuk set Slide Awal.
                        </p>
                    </div>
                    <a href="{{ route('liturgy.presentation', $schedule->id) }}" target="_blank" class="btn btn-primary fw-bold px-4 shadow-sm" style="background-color: #3b82f6; border:none;">💻 Buka Proyektor</a>
                </div>

                <div class="row g-3">
                    @foreach($allSlides as $idx => $slide)
                        <div class="col-6 col-md-4 col-xl-3">
                            <div class="preview-card" id="thumb-{{$idx}}" ondblclick="setStartSlide({{$idx}})" onclick="jumpToSlide({{$idx}})" title="Klik 1x untuk tayangkan. Klik 2x untuk set awal.">
                                <div class="preview-title">{{ $slide['judul'] }}</div>
                                <div class="preview-content">{!! nl2br(e($slide['isi'])) !!}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="font-controller" id="fontController">
                    <span class="text-secondary small me-2 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Ukuran Teks Slide:</span>
                    <button class="btn-font" onclick="changeCustomFont(-0.5)" title="Perkecil Font">-</button>
                    <div class="font-indicator" id="fontIndicator">Auto</div>
                    <button class="btn-font" onclick="changeCustomFont(0.5)" title="Perbesar Font">+</button>
                    <button class="btn-font-auto ms-2" onclick="resetCustomFont()" title="Kembali ke ukuran otomatis">Reset</button>
                </div>
            </div>
            
        </div>
    </div>

    <script>
        const scheduleId = {{ $schedule->id ?? 0 }};
        let activeSlideIndex = parseInt(localStorage.getItem('last_slide_index')) || 0;
        let customFonts = JSON.parse(localStorage.getItem('custom_fonts_' + scheduleId)) || {};

        // FORM LOGIC
        function getNextVerseNumber(containerId) {
            const container = document.getElementById(containerId);
            let maxNum = 0;
            const textareas = container.querySelectorAll('textarea');
            textareas.forEach(ta => {
                if(ta.name.includes('[bait]')) {
                    const match = ta.name.match(/\[bait\]\[(\d+)\]/);
                    if(match && parseInt(match[1]) > maxNum) { maxNum = parseInt(match[1]); }
                }
            });
            return maxNum === 0 ? 1 : maxNum + 1;
        }

        function tambahBait(itemId) {
            const containerId = 'bait-container-' + itemId;
            const nextNum = getNextVerseNumber(containerId);
            const html = `
                <div class="input-group mb-2 shadow-sm position-relative">
                    <span class="input-group-text bg-light text-secondary" style="font-size:0.8rem; min-width: 70px;">Bait ${nextNum}</span>
                    <textarea name="dynamic_content[${itemId}][bait][${nextNum}]" class="form-control" rows="3" placeholder="Teks lanjutan..."></textarea>
                    <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.parentElement.remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                </div>`;
            document.getElementById(containerId).insertAdjacentHTML('beforeend', html);
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
                        <textarea name="custom_slides[${itemId}][${slideId}][content]" class="form-control border-secondary" rows="4" placeholder="Isi konten..." required></textarea>
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
            const originalText = btn.innerHTML; btn.innerHTML = 'Memuat...'; btn.disabled = true;
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
                                const html = `
                                    <div class="input-group mb-2 shadow-sm position-relative">
                                        <span class="input-group-text bg-light text-secondary" style="font-size:0.8rem; min-width: 70px;">Bait ${verseNum}</span>
                                        <textarea name="dynamic_content[${itemId}][bait][${verseNum}]" class="form-control" rows="3">${bait.trim()}</textarea>
                                        <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3 rounded" onclick="this.parentElement.remove()" style="font-size: 14px; padding: 2px 6px;">&times;</button>
                                    </div>`;
                                container.insertAdjacentHTML('beforeend', html);
                                verseNum++;
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
            if(!query) { alert('Tulis nama kitab dan pasalnya!'); inputField.focus(); return; }
            const originalText = btn.innerHTML; btn.innerHTML = '...'; btn.disabled = true; textarea.value = "Memuat data...";
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

        // ==========================================
        // LOGIKA LIVE CONTROL & FONT
        // ==========================================
        function updateActiveThumb(index) {
            activeSlideIndex = index;
            document.querySelectorAll('.preview-card').forEach((el, i) => {
                if (i === index) el.classList.add('active');
                else el.classList.remove('active');
            });
            
            // Tampilkan font controller
            document.getElementById('fontController').classList.add('show');
            updateFontIndicator();
        }

        function setStartSlide(index) {
            localStorage.setItem('last_slide_index', index);
            updateActiveThumb(index);
            alert('Titik mulai presentasi berhasil diatur ke slide ini.');
        }

        function jumpToSlide(index) {
            localStorage.setItem('projector_command', JSON.stringify({action: 'jump', index: index, _t: Date.now()}));
            localStorage.setItem('last_slide_index', index);
            updateActiveThumb(index);
        }

        // FUNGSI CUSTOM FONT PER SLIDE
        function updateFontIndicator() {
            const indicator = document.getElementById('fontIndicator');
            if (customFonts[activeSlideIndex]) {
                indicator.innerText = customFonts[activeSlideIndex] + 'vw';
                indicator.style.color = '#fcd34d';
            } else {
                indicator.innerText = 'Auto';
                indicator.style.color = '#cbd5e1';
            }
        }

        function changeCustomFont(step) {
            let currentSize = customFonts[activeSlideIndex] ? parseFloat(customFonts[activeSlideIndex]) : 5.0; // 5.0 adalah nilai tengah default
            let newSize = (currentSize + step).toFixed(1);
            
            // Batasan ukuran (minimum 2vw, maksimum 10vw)
            if(newSize < 2.0) newSize = 2.0;
            if(newSize > 10.0) newSize = 10.0;

            customFonts[activeSlideIndex] = newSize;
            saveAndSyncFont();
        }

        function resetCustomFont() {
            delete customFonts[activeSlideIndex];
            saveAndSyncFont();
        }

        function saveAndSyncFont() {
            localStorage.setItem('custom_fonts_' + scheduleId, JSON.stringify(customFonts));
            localStorage.setItem('font_sync_trigger', Date.now()); // Picu proyektor untuk merender ulang
            updateFontIndicator();
        }

        window.addEventListener('storage', (e) => {
            if (e.key === 'last_slide_index') {
                updateActiveThumb(parseInt(e.newValue));
            }
        });

        updateActiveThumb(parseInt(localStorage.getItem('last_slide_index')) || 0);
    </script>
</body>
</html>