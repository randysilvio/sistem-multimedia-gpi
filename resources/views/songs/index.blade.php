<!DOCTYPE html>
<html lang="id">
<head>
    <title>Database Lagu - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { background-color: #f4f6f8; font-family: 'Inter', sans-serif; color: #2d3748; }
        .navbar { background-color: #1a202c !important; }
        .btn-primary-custom { background-color: #2b6cb0; color: white; border: none; font-weight: 500;}
        .btn-primary-custom:hover { background-color: #2c5282; color: white; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark shadow-sm py-3 mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold text-uppercase" style="letter-spacing: 1px;" href="#">Database Lagu Gereja</a>
            <div>
                <a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-light btn-sm px-3 me-2">Kembali ke Galeri</a>
                <a href="{{ route('songs.create') }}" class="btn btn-primary-custom btn-sm px-4">&plus; Tambah Lagu Baru</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        @if(session('success')) 
            <div class="alert alert-success border-0 border-start border-4 border-success bg-white shadow-sm">{{ session('success') }}</div> 
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-uppercase" style="color: #4a5568;">Daftar Lagu Tersimpan</h6>
                <form action="{{ route('songs.index') }}" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari Judul / Nomor..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-secondary">Cari</button>
                </form>
            </div>
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light text-secondary text-uppercase" style="font-size: 0.8rem;">
                    <tr>
                        <th class="ps-4 py-3">Buku</th>
                        <th>Nomor</th>
                        <th>Judul Lagu</th>
                        <th>Bait</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($songs as $song)
                    <tr>
                        <td class="ps-4 fw-bold text-primary">{{ $song->book ?: 'Lainnya' }}</td>
                        <td class="fw-medium">{{ $song->number ?: '-' }}</td>
                        <td class="fw-bold" style="color: #2d3748;">{{ $song->title }} {!! $song->chorus ? '<span class="badge bg-info ms-2" style="font-size:0.6rem;">Ada Reff</span>' : '' !!}</td>
                        <td class="text-muted small">{{ count($song->verses) }} Bait</td>
                        <td class="text-end pe-4">
                            <a href="{{ route('songs.print', $song->id) }}" target="_blank" class="btn btn-light border btn-sm text-success fw-medium">Cetak</a>
                            <a href="{{ route('songs.edit', $song->id) }}" class="btn btn-light border btn-sm text-primary fw-medium">Edit</a>
                            <form action="{{ route('songs.destroy', $song->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus lagu ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-light border btn-sm text-danger fw-medium">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $songs->links() }}</div>
    </div>
</body>
</html>