<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Template Master - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; padding-bottom: 100px; color:#1e293b; }
        .navbar { background-color: #0f172a !important; }
        .navbar-brand { font-weight: 700; letter-spacing: 1px; font-size: 1.1rem; }
        
        .builder-container { max-width: 800px; margin: auto; margin-top: 30px; }
        
        .block-wrapper { position: relative; }
        .block-card { background: white; border-radius: 8px; padding: 25px; border: 1px solid #e2e8f0; position: relative; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: 0.2s; }
        .block-card:focus-within { box-shadow: 0 4px 15px rgba(16, 185, 129, 0.15); border-color: #6ee7b7; }
        
        .btn-delete-block { position: absolute; top: 15px; right: 15px; background: transparent; border: 1px solid #e2e8f0; color: #ef4444; font-size: 1.2rem; cursor: pointer; width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; transition: 0.2s; line-height: 1; }
        .btn-delete-block:hover { background: #fee2e2; border-color: #fca5a5; transform: scale(1.05); }
        
        /* DESAIN GARIS SISIPKAN (ELEGAN) */
        .insert-divider { position: relative; height: 36px; display: flex; align-items: center; justify-content: center; margin-top: -12px; margin-bottom: -12px; z-index: 10; opacity: 0; transition: opacity 0.3s; }
        .block-wrapper:hover .insert-divider, .insert-divider:hover, .insert-divider.show { opacity: 1; z-index: 950; }
        .top-divider { opacity: 1; margin-bottom: 15px; margin-top: 0; height: 40px; }
        .insert-divider::before { content: ""; position: absolute; width: 100%; height: 2px; background: #cbd5e1; z-index: 1; border-radius: 2px; transition: 0.2s; }
        .block-wrapper:hover .insert-divider::before { background: #94a3b8; }
        
        .btn-insert { position: relative; z-index: 2; background: #ffffff; color: #475569; border: 1px solid #cbd5e1; border-radius: 50%; width: 34px; height: 34px; font-weight: 400; font-size: 22px; display: flex; align-items: center; justify-content: center; padding: 0; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: 0.2s; line-height: 1; }
        .btn-insert:hover, .btn-insert[aria-expanded="true"] { border-color: #10b981; color: #10b981; background: #ecfdf5; transform: scale(1.1); box-shadow: 0 4px 10px rgba(16, 185, 129, 0.15); }
        
        /* DROPDOWN KUSTOM */
        .dropdown-menu { border-radius: 8px; padding: 8px 0; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 1050 !important; min-width: 220px; }
        .dropdown-item { padding: 10px 20px; font-size: 0.85rem; font-weight: 500; color: #334155; }
        .dropdown-item:hover { background-color: #f8fafc; color: #0f172a; }
        .dropdown-divider { border-top: 1px solid #e2e8f0; margin: 5px 0; }

        /* SOLID BOTTOM TOOLBAR PROFESIONAL */
        .toolbar-menu { position: fixed; bottom: 0; left: 0; width: 100%; background: #ffffff; padding: 15px 0; border-top: 1px solid #e2e8f0; box-shadow: 0 -4px 20px rgba(0,0,0,0.04); z-index: 900; }
        
        .btn-primary-custom { background-color: #0f172a; color: white; border: none; font-weight: 600; letter-spacing: 0.5px; padding: 10px 30px; border-radius: 6px; }
        .btn-primary-custom:hover { background-color: #1e293b; color: white; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm py-3 mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-uppercase m-0 d-flex align-items-center" href="{{ route('liturgy.gallery') }}">
                <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" alt="Logo GPI" height="32" class="me-3">
                <span>Edit Template Master</span>
            </a>
            <a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-light btn-sm fw-medium px-4">Batal & Kembali</a>
        </div>
    </nav>

    <div class="container builder-container">
        <form action="{{ route('liturgy.template.update', $liturgy->id) }}" method="POST">
            @csrf
            
            <div class="card border-0 shadow-sm mb-4 rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-success mb-3 text-uppercase" style="letter-spacing: 1px; font-size:0.85rem;">Informasi Template</h6>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-secondary">Nama Template (Wajib)</label>
                        <input type="text" name="name" class="form-control form-control-lg bg-light fw-bold text-dark" value="{{ $liturgy->name }}" required>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-end mt-5 mb-3">
                <h6 class="fw-bold text-dark text-uppercase m-0" style="letter-spacing: 1px; font-size:0.85rem;">Struktur Kerangka Slide Saat Ini</h6>
                <small class="text-muted">Arahkan kursor di antara slide untuk menyisipkan</small>
            </div>

            <div id="canvas-area">
                <div class="insert-divider top-divider" id="top-anchor">
                    <div class="dropdown">
                        <button type="button" class="btn-insert" data-bs-toggle="dropdown" aria-expanded="false" title="Tambah Slide Paling Atas">&plus;</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('nyanyian', 'top')">Tambah Kerangka Nyanyian</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('alkitab', 'top')">Tambah Kerangka Bacaan Alkitab</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('votum', 'top')">Tambah Votum / Prosesi</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('aksi', 'top')">Tambah Instruksi Sikap</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('polos', 'top')">Tambah Slide Teks Bebas</a></li>
                        </ul>
                    </div>
                </div>

                @foreach($liturgy->items as $index => $item)
                    @php
                        $titleLower = strtolower($item->title);
                        $cardColor = '#718096'; $typeLabel = 'SLIDE TEKS BEBAS'; $type = 'polos';
                        if (str_contains($titleLower, 'nyanyian') || str_contains($titleLower, 'pujian')) {
                            $cardColor = '#2b6cb0'; $typeLabel = 'NYANYIAN JEMAAT'; $type = 'nyanyian';
                        } elseif (str_contains($titleLower, 'alkitab') || str_contains($titleLower, 'bacaan')) {
                            $cardColor = '#2c5282'; $typeLabel = 'BACAAN ALKITAB'; $type = 'alkitab';
                        } elseif (str_contains($titleLower, 'votum') || str_contains($titleLower, 'prosesi') || str_contains($titleLower, 'pengakuan')) {
                            $cardColor = '#4a5568'; $typeLabel = 'VOTUM / PROSESI / PENGAKUAN'; $type = 'votum';
                        } elseif (str_contains($titleLower, 'sikap') || str_contains($titleLower, 'aksi')) {
                            $cardColor = '#c53030'; $typeLabel = 'INSTRUKSI SIKAP JEMAAT'; $type = 'aksi';
                        }
                    @endphp

                    <div class="block-wrapper" id="wrapper-old-{{ $index }}">
                        <div class="block-card" style="border-top: 4px solid {{ $cardColor }};">
                            <button type="button" class="btn-delete-block" onclick="this.closest('.block-wrapper').remove()" title="Hapus Kerangka">&times;</button>
                            <div class="mb-3 pb-2">
                                <span class="badge text-uppercase" style="background-color: {{ $cardColor }}; letter-spacing: 0.5px;">{{ $typeLabel }}</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold text-muted">Judul Sesi</label>
                                    <input type="text" name="blocks[old_{{ $index }}][title]" class="form-control fw-bold" value="{{ $item->title }}" required>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label small fw-bold text-muted">Teks Bawaan (Opsional)</label>
                                    <textarea name="blocks[old_{{ $index }}][content]" class="form-control bg-light" rows="2">{{ $item->static_content }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="insert-divider">
                            <div class="dropdown">
                                <button type="button" class="btn-insert" data-bs-toggle="dropdown" aria-expanded="false" title="Sisipkan Slide Di Sini">&plus;</button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('nyanyian', 'old-{{ $index }}')">Tambah Kerangka Nyanyian</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('alkitab', 'old-{{ $index }}')">Tambah Kerangka Bacaan Alkitab</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('votum', 'old-{{ $index }}')">Tambah Votum / Prosesi</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('aksi', 'old-{{ $index }}')">Tambah Instruksi Sikap</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('polos', 'old-{{ $index }}')">Tambah Slide Teks Bebas</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="toolbar-menu">
                <div class="container d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary-custom">SIMPAN PERUBAHAN TEMPLATE</button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let blockCount = {{ $liturgy->items->count() }};

        function insertBlock(type, referenceId) {
            blockCount++;
            const newId = blockCount;
            let cardColor = '';
            let typeLabel = '';
            let defaultTitle = '';
            let defaultContent = '';

            if (type === 'nyanyian') { cardColor = '#2b6cb0'; typeLabel = 'NYANYIAN JEMAAT'; defaultTitle = 'Nyanyian Jemaat'; } 
            else if (type === 'alkitab') { cardColor = '#2c5282'; typeLabel = 'BACAAN ALKITAB'; defaultTitle = 'Pelayanan Firman'; }
            else if (type === 'votum') { cardColor = '#4a5568'; typeLabel = 'VOTUM / PROSESI / PENGAKUAN'; defaultTitle = 'Votum dan Salam'; defaultContent = 'Pertolongan kita adalah dalam nama Tuhan...'; }
            else if (type === 'aksi') { cardColor = '#c53030'; typeLabel = 'INSTRUKSI SIKAP JEMAAT'; defaultTitle = 'Sikap Jemaat'; defaultContent = '(Jemaat Berdiri)'; }
            else { cardColor = '#718096'; typeLabel = 'SLIDE TEKS BEBAS'; defaultTitle = 'Judul Slide...'; }

            const blockHtml = `
                <div class="block-card" style="border-top: 4px solid ${cardColor};">
                    <button type="button" class="btn-delete-block" onclick="this.closest('.block-wrapper').remove()" title="Hapus Kerangka">&times;</button>
                    <div class="mb-3 pb-2">
                        <span class="badge text-uppercase" style="background-color: ${cardColor}; letter-spacing: 0.5px;">${typeLabel}</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted">Judul Sesi</label>
                            <input type="text" name="blocks[${newId}][title]" class="form-control fw-bold" value="${defaultTitle}" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-muted">Teks Bawaan (Opsional)</label>
                            <textarea name="blocks[${newId}][content]" class="form-control bg-light" rows="2">${defaultContent}</textarea>
                        </div>
                    </div>
                </div>
            `;

            const dividerHtml = `
                <div class="insert-divider">
                    <div class="dropdown">
                        <button type="button" class="btn-insert" data-bs-toggle="dropdown" aria-expanded="false" title="Sisipkan Slide Di Sini">&plus;</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('nyanyian', '${newId}')">Tambah Kerangka Nyanyian</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('alkitab', '${newId}')">Tambah Kerangka Bacaan Alkitab</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('votum', '${newId}')">Tambah Votum / Prosesi</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('aksi', '${newId}')">Tambah Instruksi Sikap</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="insertBlock('polos', '${newId}')">Tambah Slide Teks Bebas</a></li>
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
    </script>
</body>
</html>