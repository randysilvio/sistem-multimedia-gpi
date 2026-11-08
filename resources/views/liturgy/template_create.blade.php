@extends('layouts.admin')

@section('eyebrow', 'Template Liturgi')
@section('title')
Template Baru
@endsection

@section('actions')
<a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-secondary">Batal</a>
@endsection

@section('content')


    <div class="container builder-container">
        <form action="{{ route('liturgy.template.store') }}" method="POST">
            @csrf
            
            <div class="card border-0 shadow-sm mb-4 rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-success mb-3 text-uppercase" style="letter-spacing: 1px; font-size:0.85rem;">Informasi Template</h6>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-secondary">Nama Template (Wajib)</label>
                        <input type="text" name="name" class="form-control form-control-lg bg-light fw-bold text-dark" placeholder="Cth: Tata Ibadah Perjamuan Kudus" required>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-end mt-5 mb-3">
                <h6 class="fw-bold text-dark text-uppercase m-0" style="letter-spacing: 1px; font-size:0.85rem;">Struktur Kerangka Slide</h6>
                <small class="text-muted">Arahkan kursor di antara slide untuk menyisipkan</small>
            </div>
            
            <div id="canvas-area">
                <div class="insert-divider top-divider" id="top-anchor">
                    <div class="dropdown">
                        <button type="button" class="btn-insert" data-bs-toggle="dropdown" aria-expanded="false" title="Tambah Slide Paling Atas">&plus;</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" onclick="insertBlock('nyanyian', 'top')">Tambah Kerangka Nyanyian</a></li>
                            <li><a class="dropdown-item" onclick="insertBlock('alkitab', 'top')">Tambah Kerangka Bacaan Alkitab</a></li>
                            <li><a class="dropdown-item" onclick="insertBlock('polos', 'top')">Tambah Slide Teks Bebas / Instruksi</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="toolbar-menu">
                <div class="container d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary btn-primary-custom">Simpan template master</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
        let blockCount = 0;

        function insertBlock(type, referenceId) {
            blockCount++;
            const newId = blockCount;
            let cardColor = '';
            let typeLabel = '';
            let defaultTitle = '';
            let placeholderTitle = '';
            let isTitleRequired = '';

            // Palette Warna & Default untuk 3 Opsi
            if (type === 'nyanyian') { 
                cardColor = '#2b6cb0'; 
                typeLabel = 'KERANGKA NYANYIAN'; 
                defaultTitle = 'Nyanyian Jemaat'; 
                isTitleRequired = 'required';
            } 
            else if (type === 'alkitab') { 
                cardColor = '#2c5282'; 
                typeLabel = 'KERANGKA BACAAN ALKITAB'; 
                defaultTitle = 'Bacaan Alkitab'; 
                isTitleRequired = 'required';
            }
            else { 
                cardColor = '#718096'; 
                typeLabel = 'KERANGKA TEKS BEBAS / INSTRUKSI'; 
                defaultTitle = ''; 
                placeholderTitle = 'Kosongkan jika untuk Votum/Sikap Jemaat (Teks di Tengah)';
                isTitleRequired = ''; // Boleh kosong untuk bagian teks bebas
            }

            const blockHtml = `
                <div class="block-card" style="border-top: 4px solid ${cardColor};">
                    <button type="button" class="btn-delete-block" onclick="this.closest('.block-wrapper').remove()" title="Hapus Kerangka">&times;</button>
                    <div class="mb-3 pb-2 d-flex justify-content-between align-items-center">
                        <span class="badge text-uppercase" style="background-color: ${cardColor}; letter-spacing: 0.5px;">${typeLabel}</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted">Judul Sesi Default</label>
                            <input type="text" name="blocks[${newId}][title]" class="form-control fw-bold" value="${defaultTitle}" placeholder="${placeholderTitle}" ${isTitleRequired}>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-muted">Teks Bawaan (Opsional)</label>
                            <textarea name="blocks[${newId}][content]" class="form-control bg-light" rows="2" placeholder="Kosongkan agar diisi waktu jadwal dibuat..."></textarea>
                        </div>
                    </div>
                </div>
            `;

            const dividerHtml = `
                <div class="insert-divider">
                    <div class="dropdown">
                        <button type="button" class="btn-insert" data-bs-toggle="dropdown" aria-expanded="false" title="Sisipkan Slide Di Sini">&plus;</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" onclick="insertBlock('nyanyian', '${newId}')">Tambah Kerangka Nyanyian</a></li>
                            <li><a class="dropdown-item" onclick="insertBlock('alkitab', '${newId}')">Tambah Kerangka Bacaan Alkitab</a></li>
                            <li><a class="dropdown-item" onclick="insertBlock('polos', '${newId}')">Tambah Slide Teks Bebas / Instruksi</a></li>
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

        window.onload = function() {
            insertBlock('nyanyian', 'top'); // Default 1 kerangka pertama
        };
    </script>
@endpush
