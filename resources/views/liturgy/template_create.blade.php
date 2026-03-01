<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Template Master - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; padding-bottom: 120px; color:#1e293b; }
        .navbar { background-color: #0f172a !important; }
        
        .builder-container { max-width: 800px; margin: auto; margin-top: 40px; }
        .block-card { background: white; border-radius: 8px; padding: 25px; margin-bottom: 15px; border: 1px solid #e2e8f0; position: relative; border-left: 4px solid #10b981; }
        
        .toolbar-menu { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: #1e293b; padding: 10px 20px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); display: flex; gap: 8px; z-index: 1000; align-items: center; }
        .btn-add { background: #334155; color: #e2e8f0; border: none; font-size: 0.8rem; font-weight: 600; cursor: pointer; padding: 8px 15px; border-radius: 4px; transition: 0.2s; }
        .btn-add:hover { background: #475569; color: #fff; }
        .btn-save { background: #10b981; color: white; border: none; font-size: 0.85rem; font-weight: 700; padding: 8px 25px; border-radius: 4px; margin-left: 10px; transition: 0.2s;}
        .btn-save:hover { background: #059669; }
        
        .btn-delete-block { position: absolute; top: 15px; right: 15px; color: #cbd5e1; background: none; border: none; font-size: 1.2rem; cursor: pointer; }
        .btn-delete-block:hover { color: #ef4444; }
        
        .form-label { font-size: 0.75rem; letter-spacing: 1px; color: #64748b; font-weight: 700; text-transform: uppercase; }
        textarea { resize: vertical; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark shadow-sm py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand fw-bold m-0" href="{{ route('liturgy.gallery') }}">SISTEM MULTIMEDIA</a>
            <a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-light btn-sm fw-medium px-4">Batal & Kembali</a>
        </div>
    </nav>

    <div class="container builder-container">
        <div class="mb-4 pb-2 border-bottom">
            <h4 class="fw-bold text-dark">Rancang Template Master</h4>
            <p class="text-secondary small">Susun kerangka baku ibadah. Lagu dan teks spesifik bisa dikosongkan untuk diisi nanti saat jadwal dibuat.</p>
        </div>

        <form action="{{ route('liturgy.template.store') }}" method="POST" id="templateForm">
            @csrf
            
            <div class="mb-4">
                <label class="form-label text-dark">Nama Template (Wajib)</label>
                <input type="text" name="name" class="form-control form-control-lg fw-bold" placeholder="Contoh: Tata Ibadah Perjamuan Kudus" required>
            </div>

            <div id="canvas-area"></div>
        </form>
    </div>

    <div class="toolbar-menu">
        <span class="text-white small fw-medium me-2">Tambahkan:</span>
        <button type="button" class="btn-add" onclick="addBlock('Nyanyian Jemaat', '')">Lagu</button>
        <button type="button" class="btn-add" onclick="addBlock('Pembacaan Alkitab', '')">Alkitab</button>
        <button type="button" class="btn-add" onclick="addBlock('Votum dan Salam', 'Pertolongan kita adalah dalam nama Tuhan...')">Votum</button>
        <button type="button" class="btn-add" onclick="addBlock('Pengakuan Iman Rasuli', 'Aku percaya kepada Allah Bapa yang Mahakuasa...')">Pengakuan Iman</button>
        <button type="button" class="btn-add" onclick="addBlock('Sikap Jemaat', '(Jemaat Berdiri)')">Sikap</button>
        <button type="button" class="btn-add" onclick="addBlock('', '')">Lainnya</button>
        <div style="width: 1px; height: 20px; background: #475569; margin: 0 5px;"></div>
        <button type="submit" form="templateForm" class="btn-save">SIMPAN TEMPLATE</button>
    </div>

<script>
    let blockCount = 0;

    function addBlock(defaultTitle, defaultContent) {
        blockCount++;
        const canvas = document.getElementById('canvas-area');
        const blockId = 'block-' + blockCount;

        const htmlContent = `
            <div class="row">
                <div class="col-md-5 mb-3 mb-md-0">
                    <label class="form-label">Tipe / Judul Slide</label>
                    <input type="text" name="blocks[${blockCount}][title]" class="form-control fw-bold text-dark" placeholder="Contoh: Nyanyian Persiapan" value="${defaultTitle}" required>
                </div>
                <div class="col-md-7">
                    <label class="form-label">Teks Bawaan (Opsional)</label>
                    <textarea name="blocks[${blockCount}][content]" class="form-control bg-light" rows="3" placeholder="Biarkan kosong jika ingin diisi nanti saat jadwal dibuat...">${defaultContent}</textarea>
                </div>
            </div>
        `;

        const block = document.createElement('div');
        block.className = 'block-card';
        block.id = blockId;
        block.innerHTML = `<button type="button" class="btn-delete-block" onclick="document.getElementById('${blockId}').remove()">&times;</button>${htmlContent}`;
        canvas.appendChild(block);
        block.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
</script>
</body>
</html>