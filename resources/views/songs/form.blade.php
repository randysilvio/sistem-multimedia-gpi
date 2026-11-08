@extends('layouts.admin')

@section('eyebrow', 'Database Lagu')
@section('title')
{{ isset($song) ? 'Edit Lagu' : 'Tambah Lagu' }}
@endsection

@section('actions')
<a href="{{ route('songs.index') }}" class="btn btn-outline-secondary">Batal</a>
@endsection

@section('content')


    <div class="container" style="max-width: 800px;">
        <h4 class="fw-bold text-dark mb-4">{{ isset($song) ? 'Edit Data Lagu' : 'Tambah Lagu Baru' }}</h4>

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm border-0 border-start border-4 border-danger mb-4">
                <strong>Penyimpanan Gagal!</strong> Periksa isian berikut:
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ isset($song) ? route('songs.update', $song->id) : route('songs.store') }}" method="POST" class="card card-custom p-4 p-md-5">
            @csrf
            @if(isset($song)) @method('PUT') @endif

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Buku Nyanyian</label>
                    <select name="book" class="form-select bg-light">
                        <option value="KJ" {{ (isset($song) && $song->book == 'KJ') ? 'selected' : '' }}>KJ</option>
                        <option value="NKB" {{ (isset($song) && $song->book == 'NKB') ? 'selected' : '' }}>NKB</option>
                        <option value="PKJ" {{ (isset($song) && $song->book == 'PKJ') ? 'selected' : '' }}>PKJ</option>
                        <option value="NR" {{ (isset($song) && $song->book == 'NR') ? 'selected' : '' }}>NR</option>
                        <option value="DSL" {{ (isset($song) && $song->book == 'DSL') ? 'selected' : '' }}>DSL</option>
                        <option value="GPM" {{ (isset($song) && $song->book == 'GPM') ? 'selected' : '' }}>GPM</option>
                        <option value="BEBAS" {{ (isset($song) && $song->book == 'BEBAS') ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nomor Lagu</label>
                    <input type="text" name="number" class="form-control bg-light" value="{{ old('number', $song->number ?? '') }}" placeholder="Cth: 15">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Judul Lagu <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control fw-bold text-primary" value="{{ old('title', $song->title ?? '') }}" placeholder="Contoh: Suci, Suci, Suci" required>
                </div>
            </div>

            <hr class="text-secondary mb-4 opacity-25">

            <div class="mb-4">
                <label class="form-label">Teks Reff / Chorus (Opsional)</label>
                <textarea name="chorus" class="form-control bg-light" rows="3" placeholder="Jika lagu ini memiliki Reff yang dinyanyikan berulang, tulis di sini. Sistem akan otomatis menyisipkannya setelah setiap bait.">{{ old('chorus', $song->chorus ?? '') }}</textarea>
            
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" name="is_incomplete" value="1" id="is_incomplete" @checked(old('is_incomplete', $song->is_incomplete ?? false))>
                    <label class="form-check-label small" for="is_incomplete">Lirik belum lengkap (diingatkan di Cek Kesiapan)</label>
                </div>
            </div>

            <label class="form-label mb-1">Lirik Per Bait <span class="text-danger">*</span></label>
            <p class="small text-muted mb-3">Nomor bait bisa diubah. Contoh: bila hanya bait 1 dan 7 yang dinyanyikan, isi nomor <b>1</b> dan <b>7</b> agar di layar tampil "Bait 7".</p>
            <div id="verses-container">
                @php $numbered = isset($song) ? $song->numberedVerses() : [1 => '']; @endphp
                @foreach($numbered as $num => $verse)
                    <div class="verse-box verse-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="d-flex align-items-center gap-2 small fw-bold text-secondary mb-0">
                                Bait
                                <input type="number" name="verse_numbers[]" class="form-control form-control-sm verse-number" value="{{ $num }}" min="1" max="99" style="width: 72px;">
                            </label>
                            @if(!$loop->first || count($numbered) > 1)
                                <button type="button" class="btn btn-sm text-danger p-0 fw-bold hover-opacity" onclick="removeVerse(this)">Hapus</button>
                            @endif
                        </div>
                        <textarea name="verses[]" class="form-control bg-white" rows="3" placeholder="Ketik lirik bait di sini..." required>{{ $verse }}</textarea>
                    </div>
                @endforeach
            </div>

            <button type="button" class="btn btn-outline-secondary fw-medium mt-2 mb-5 w-100 border-dashed" style="border-style: dashed;" onclick="addVerse()">
                &plus; Tambah Lirik Bait Berikutnya
            </button>

            <button type="submit" class="btn btn-primary btn-lg w-100">Simpan ke database</button>
        </form>
    </div>
@endsection

@push('scripts')
<script>
        // Nomor bait baru = nomor terbesar + 1 (nomor yang sudah ada tidak diubah otomatis)
        function addVerse() {
            const container = document.getElementById('verses-container');
            const max = Math.max(0, ...[...container.querySelectorAll('.verse-number')].map((i) => parseInt(i.value, 10) || 0));
            const html = `
                <div class="verse-box verse-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="d-flex align-items-center gap-2 small fw-bold text-secondary mb-0">
                            Bait
                            <input type="number" name="verse_numbers[]" class="form-control form-control-sm verse-number" value="${max + 1}" min="1" max="99" style="width: 72px;">
                        </label>
                        <button type="button" class="btn btn-sm text-danger p-0 fw-bold" onclick="removeVerse(this)">Hapus</button>
                    </div>
                    <textarea name="verses[]" class="form-control bg-white" rows="3" placeholder="Ketik lirik bait lanjutan..." required></textarea>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            container.lastElementChild.querySelector('textarea').focus();
        }

        function removeVerse(btn) {
            btn.closest('.verse-item').remove();
        }
    </script>
@endpush
