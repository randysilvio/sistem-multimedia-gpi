<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Warta Sinode - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; color: #1e293b; padding-bottom: 50px; }
        .navbar { background-color: #0f172a !important; }
        .image-preview { width: 100%; height: 180px; object-fit: cover; border-radius: 8px 8px 0 0; background: #e2e8f0; }
        .card-warta { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: 0.2s; }
        .card-warta:hover { transform: translateY(-3px); border-color: #3b82f6; }
        .info-tip { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-size: 0.9rem; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark shadow-sm py-3 mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand fw-bold" href="{{ route('liturgy.gallery') }}">
                <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" height="30" class="me-2">
                KELOLA WARTA SINODE
            </a>
            <a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-light btn-sm px-4">Kembali ke Galeri</a>
        </div>
    </nav>

    <div class="container">
        <div class="info-tip shadow-sm">
            <strong>Resolusi Ideal:</strong> Gunakan gambar ukuran <strong>1920x1080 (16:9)</strong> agar pas memenuhi layar proyektor tanpa sisa hitam. Semua gambar di bawah akan tayang otomatis dalam 1 slide slideshow.
        </div>

        @if(session('success')) 
            <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div> 
        @endif

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white fw-bold text-primary py-3">Unggah Warta Baru</div>
                    <div class="card-body">
                        <form action="{{ route('announcement.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold">File Gambar (JPG/PNG/GIF)</label>
                                <input type="file" name="image" class="form-control" accept="image/*" required>
                                <small class="text-muted" style="font-size:10px;">Max 100MB. Mendukung GIF Animasi.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Caption (Opsional)</label>
                                <input type="text" name="title" class="form-control" placeholder="Cth: Jadwal Persidangan">
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label small fw-bold">Urutan</label>
                                    <input type="number" name="order_num" class="form-control" value="0">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label small fw-bold">Durasi (Detik)</label>
                                    <input type="number" name="duration" class="form-control" value="5" min="1">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mt-2">UPLOAD KE PROYEKTOR</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white fw-bold py-3">Antrean Slideshow Aktif</div>
                    <div class="card-body">
                        <div class="row g-3">
                            @forelse($announcements as $ann)
                                <div class="col-md-6">
                                    <div class="card-warta">
                                        <img src="{{ asset('storage/' . $ann->image_path) }}" class="image-preview">
                                        <div class="p-3">
                                            <div class="fw-bold text-truncate" style="font-size: 0.9rem;">{{ $ann->title ?? 'Tanpa Caption' }}</div>
                                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                                <small class="text-primary fw-bold">Urutan: {{ $ann->order_num }} | {{ $ann->duration }}s</small>
                                                <form action="{{ route('announcement.destroy', $ann->id) }}" method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3 py-0" onclick="return confirm('Hapus gambar ini?')">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5 text-muted">Belum ada warta untuk ditayangkan.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>