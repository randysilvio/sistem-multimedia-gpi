@extends('layouts.admin')

@section('eyebrow', 'Ibadah')
@section('title')
Warta Sinode
@endsection

@section('content')


    <div class="container">
        <div class="info-tip shadow-sm">
            <strong>Resolusi Ideal:</strong> Gunakan gambar ukuran <strong>1920x1080 (16:9)</strong> agar pas memenuhi layar proyektor tanpa sisa hitam. Semua gambar di bawah akan tayang otomatis dalam 1 slide slideshow.
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white fw-bold text-primary py-3">Unggah Warta Baru</div>
                    <div class="card-body">
                        <form action="{{ route('announcement.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold">File Gambar (JPG/PNG/GIF)</label>
                                <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" required>
                                <small class="text-muted" style="font-size:10px;">Maks. 20 MB · JPG, PNG, WEBP, atau GIF animasi.</small>
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
                            <button type="submit" class="btn btn-primary w-100 mt-2">Unggah ke slideshow</button>
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
@endsection
