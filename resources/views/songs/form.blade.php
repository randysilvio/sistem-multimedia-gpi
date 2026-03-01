<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($song) ? 'Edit Lagu' : 'Tambah Lagu' }} - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { background-color: #f4f6f8; font-family: 'Inter', sans-serif; color: #2d3748; padding-bottom: 50px; }
        .navbar { background-color: #1a202c !important; }
        .card-custom { border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 12px; }
        .form-control:focus, .form-select:focus { border-color: #3182ce; box-shadow: 0 0 0 0.2rem rgba(49, 130, 206, 0.15); }
        .verse-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 15px; position: relative; transition: 0.2s;}
        .verse-box:hover { border-color: #cbd5e0; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        textarea { resize: vertical; }
        .btn-primary-custom { background-color: #2b6cb0; color: white; border: none; transition: 0.2s;}
        .btn-primary-custom:hover { background-color: #2c5282; color: white; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark shadow-sm py-3 mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-uppercase m-0 d-flex align-items-center" href="{{ route('songs.index') }}">
                <span class="fw-bold" style="font-size: 1.1rem; letter-spacing: 1px;">Database Lagu Gereja</span>
            </a>
            <a href="{{ route('songs.index') }}" class="btn btn-outline-light btn-sm fw-medium px-4">Batal & Kembali</a>
        </div>
    </nav>

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
                    <label class="form-label fw-bold text-secondary small text-uppercase">Buku Nyanyian</label>
                    <select name="book" class="form-select bg-light">
                        <option value="KJ" {{ (isset($song) && $song->book == 'KJ') ? 'selected' : '' }}>KJ</option>
                        <option value="NKB" {{ (isset($song) && $song->book == 'NKB') ? 'selected' : '' }}>NKB</option>
                        <option value="PKJ" {{ (isset($song) && $song->book == 'PKJ') ? 'selected' : '' }}>PKJ</option>
                        <option value="NR" {{ (isset($song) && $song->book == 'NR') ? 'selected' : '' }}>NR</option>
                        <option value="BEBAS" {{ (isset($song) && $song->book == 'BEBAS') ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-secondary small text-uppercase">Nomor Lagu</label>
                    <input type="text" name="number" class="form-control bg-light" value="{{ old('number', $song->number ?? '') }}" placeholder="Cth: 15">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary small text-uppercase">Judul Lagu <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control fw-bold text-primary" value="{{ old('title', $song->title ?? '') }}" placeholder="Contoh: Suci, Suci, Suci" required>
                </div>
            </div>

            <hr class="text-secondary mb-4 opacity-25">

            <div class="mb-4">
                <label class="form-label fw-bold text-info text-uppercase small">Teks Reff / Chorus (Opsional)</label>
                <textarea name="chorus" class="form-control bg-light" rows="3" placeholder="Jika lagu ini memiliki Reff yang dinyanyikan berulang, tulis di sini. Sistem akan otomatis menyisipkannya setelah setiap bait.">{{ old('chorus', $song->chorus ?? '') }}</textarea>
            </div>

            <label class="form-label fw-bold text-dark mb-3 text-uppercase small">Lirik Per Bait <span class="text-danger">*</span></label>
            <div id="verses-container">
                @if(isset($song) && count($song->verses) > 0)
                    @foreach($song->verses as $index => $verse)
                        <div class="verse-box verse-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-secondary verse-badge px-3 py-2">Bait {{ $index + 1 }}</span>
                                <button type="button" class="btn btn-sm text-danger p-0 fw-bold hover-opacity" onclick="removeVerse(this)">Hapus</button>
                            </div>
                            <textarea name="verses[]" class="form-control bg-white" rows="3" required>{{ $verse }}</textarea>
                        </div>
                    @endforeach
                @else
                    <div class="verse-box verse-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-secondary verse-badge px-3 py-2">Bait 1</span>
                        </div>
                        <textarea name="verses[]" class="form-control bg-white" rows="3" placeholder="Ketik lirik bait pertama di sini..." required></textarea>
                    </div>
                @endif
            </div>

            <button type="button" class="btn btn-outline-secondary fw-medium mt-2 mb-5 w-100 border-dashed" style="border-style: dashed;" onclick="addVerse()">
                &plus; Tambah Lirik Bait Berikutnya
            </button>

            <button type="submit" class="btn btn-primary-custom btn-lg w-100 py-3 fw-bold shadow-sm text-uppercase" style="letter-spacing: 1px;">Simpan ke Database</button>
        </form>
    </div>

    <script>
        function updateVerseBadges() {
            const badges = document.querySelectorAll('.verse-badge');
            badges.forEach((badge, index) => {
                badge.innerText = 'Bait ' + (index + 1);
            });
        }

        function addVerse() {
            const container = document.getElementById('verses-container');
            const count = container.children.length + 1;
            const html = `
                <div class="verse-box verse-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-secondary verse-badge px-3 py-2">Bait ${count}</span>
                        <button type="button" class="btn btn-sm text-danger p-0 fw-bold" onclick="removeVerse(this)">Hapus</button>
                    </div>
                    <textarea name="verses[]" class="form-control bg-white" rows="3" placeholder="Ketik lirik bait lanjutan..." required></textarea>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            updateVerseBadges(); // Panggil fungsi untuk memastikan nomor urut
        }

        function removeVerse(btn) {
            btn.closest('.verse-item').remove();
            updateVerseBadges(); // Mengatur ulang nomor bait setelah ada yang dihapus
        }
    </script>
</body>
</html>