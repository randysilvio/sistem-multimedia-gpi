<!DOCTYPE html>
<html lang="id">
<head>
    <title>{{ isset($song) ? 'Edit Lagu' : 'Tambah Lagu' }} - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { background-color: #f4f6f8; font-family: 'Inter', sans-serif; color: #2d3748; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .form-control:focus, .form-select:focus { border-color: #3182ce; box-shadow: none; }
        .verse-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; margin-bottom: 10px; position: relative;}
    </style>
</head>
<body>
    <div class="container mt-5" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">{{ isset($song) ? 'Edit Lagu' : 'Tambah Lagu Baru' }}</h4>
            <a href="{{ route('songs.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>

        <form action="{{ isset($song) ? route('songs.update', $song->id) : route('songs.store') }}" method="POST" class="card p-4">
            @csrf
            @if(isset($song)) @method('PUT') @endif

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-medium small">Buku Nyanyian</label>
                    <select name="book" class="form-select">
                        <option value="KJ" {{ (isset($song) && $song->book == 'KJ') ? 'selected' : '' }}>KJ</option>
                        <option value="NKB" {{ (isset($song) && $song->book == 'NKB') ? 'selected' : '' }}>NKB</option>
                        <option value="PKJ" {{ (isset($song) && $song->book == 'PKJ') ? 'selected' : '' }}>PKJ</option>
                        <option value="NR" {{ (isset($song) && $song->book == 'NR') ? 'selected' : '' }}>NR</option>
                        <option value="BEBAS" {{ (isset($song) && $song->book == 'BEBAS') ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium small">Nomor Lagu</label>
                    <input type="text" name="number" class="form-control" value="{{ $song->number ?? '' }}" placeholder="Cth: 15">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium small">Judul Lagu (Wajib)</label>
                    <input type="text" name="title" class="form-control fw-bold text-primary" value="{{ $song->title ?? '' }}" required>
                </div>
            </div>

            <hr class="text-secondary mb-4">

            <div class="mb-4">
                <label class="form-label fw-bold text-info">Teks Reff / Chorus (Opsional)</label>
                <textarea name="chorus" class="form-control" rows="3" placeholder="Jika lagu ini memiliki Reff yang dinyanyikan berulang, tulis di sini. Sistem akan otomatis menyisipkannya setelah setiap bait.">{{ $song->chorus ?? '' }}</textarea>
            </div>

            <label class="form-label fw-bold text-dark mb-3">Bait Nyanyian</label>
            <div id="verses-container">
                @if(isset($song) && count($song->verses) > 0)
                    @foreach($song->verses as $index => $verse)
                        <div class="verse-box">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-secondary">Bait {{ $index + 1 }}</span>
                                <button type="button" class="btn btn-sm text-danger p-0 fw-bold" onclick="this.parentElement.parentElement.remove()">Hapus</button>
                            </div>
                            <textarea name="verses[]" class="form-control bg-white" rows="3" required>{{ $verse }}</textarea>
                        </div>
                    @endforeach
                @else
                    <div class="verse-box">
                        <span class="badge bg-secondary mb-2">Bait 1</span>
                        <textarea name="verses[]" class="form-control bg-white" rows="3" placeholder="Ketik lirik bait pertama..." required></textarea>
                    </div>
                @endif
            </div>

            <button type="button" class="btn btn-light border fw-medium text-primary mt-2 mb-4" onclick="addVerse()">&plus; Tambah Bait Selanjutnya</button>

            <button type="submit" class="btn btn-primary py-3 fw-bold" style="background: #2b6cb0;">Simpan ke Database</button>
        </form>
    </div>

    <script>
        function addVerse() {
            const container = document.getElementById('verses-container');
            const count = container.children.length + 1;
            const html = `
                <div class="verse-box">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-secondary">Bait Tambahan</span>
                        <button type="button" class="btn btn-sm text-danger p-0 fw-bold" onclick="this.parentElement.parentElement.remove()">Hapus</button>
                    </div>
                    <textarea name="verses[]" class="form-control bg-white" rows="3" placeholder="Ketik lirik..."></textarea>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }
    </script>
</body>
</html>