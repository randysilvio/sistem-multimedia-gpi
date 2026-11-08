@extends('layouts.admin')

@section('eyebrow', 'Template Liturgi')
@section('title')
Edit Template: {{ $liturgy->name }}
@endsection

@section('actions')
<a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-secondary">Batal</a>
@endsection

@section('content')
@php
    // Kategori warna per bagian: aturannya sama dengan pembuat slide ("Doa Pembacaan Alkitab" = teks).
    // Closure (bukan fungsi global) agar aman bila halaman dirender lebih dari sekali dalam satu proses.
    $categoryStyle = fn ($title) => match (\App\Services\SlideBuilder::itemType($title)) {
        'nyanyian' => ['color' => '#2b6cb0', 'label' => 'KERANGKA NYANYIAN'],
        'alkitab' => ['color' => '#2c5282', 'label' => 'KERANGKA BACAAN ALKITAB'],
        default => ['color' => '#718096', 'label' => 'KERANGKA TEKS BEBAS / INSTRUKSI'],
    };
    @endphp


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

                @foreach($liturgy->items as $index => $item)
                    @php
                        $style = $categoryStyle($item->title);
                        $blockId = 'old_' . $item->id;
                    @endphp
                    
                    <div class="block-wrapper" id="wrapper-{{ $blockId }}">
                        <div class="block-card" style="border-top: 4px solid {{ $style['color'] }};">
                            <button type="button" class="btn-delete-block" onclick="this.closest('.block-wrapper').remove()" title="Hapus Kerangka">&times;</button>
                            <div class="mb-3 pb-2 d-flex justify-content-between align-items-center">
                                <span class="badge text-uppercase" style="background-color: {{ $style['color'] }}; letter-spacing: 0.5px;">{{ $style['label'] }}</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold text-muted">Judul Sesi Default</label>
                                    <input type="text" name="blocks[{{ $index }}][title]" class="form-control fw-bold" value="{{ $item->title }}" placeholder="Kosongkan jika untuk Teks di Tengah">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label small fw-bold text-muted">Teks Bawaan (Opsional)</label>
                                    <textarea name="blocks[{{ $index }}][content]" class="form-control bg-light" rows="2" placeholder="Kosongkan agar diisi waktu jadwal dibuat...">{{ $item->static_content }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="insert-divider">
                            <div class="dropdown">
                                <button type="button" class="btn-insert" data-bs-toggle="dropdown" aria-expanded="false" title="Sisipkan Slide Di Sini">&plus;</button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" onclick="insertBlock('nyanyian', '{{ $blockId }}')">Tambah Kerangka Nyanyian</a></li>
                                    <li><a class="dropdown-item" onclick="insertBlock('alkitab', '{{ $blockId }}')">Tambah Kerangka Bacaan Alkitab</a></li>
                                    <li><a class="dropdown-item" onclick="insertBlock('polos', '{{ $blockId }}')">Tambah Slide Teks Bebas / Instruksi</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="toolbar-menu">
                <div class="container d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary btn-primary-custom">Simpan perubahan template</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
        let blockCount = {{ count($liturgy->items) * 10 }}; 

        function insertBlock(type, referenceId) {
            blockCount++;
            const newId = 'new_' + blockCount;
            let cardColor = '';
            let typeLabel = '';
            let defaultTitle = '';
            let placeholderTitle = '';

            if (type === 'nyanyian') { 
                cardColor = '#2b6cb0'; typeLabel = 'KERANGKA NYANYIAN'; defaultTitle = 'Nyanyian Jemaat'; 
            } else if (type === 'alkitab') { 
                cardColor = '#2c5282'; typeLabel = 'KERANGKA BACAAN ALKITAB'; defaultTitle = 'Bacaan Alkitab'; 
            } else { 
                cardColor = '#718096'; typeLabel = 'KERANGKA TEKS BEBAS / INSTRUKSI'; defaultTitle = ''; 
                placeholderTitle = 'Kosongkan jika untuk Votum/Sikap Jemaat (Teks di Tengah)';
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
                            <input type="text" name="blocks[${newId}][title]" class="form-control fw-bold" value="${defaultTitle}" placeholder="${placeholderTitle}">
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
    </script>
@endpush
