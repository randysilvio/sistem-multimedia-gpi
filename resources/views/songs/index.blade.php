<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Lagu - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { background-color: #f4f6f8; font-family: 'Inter', sans-serif; color: #2d3748; }
        .navbar { background-color: #1a202c !important; }
        .btn-primary-custom { background-color: #2b6cb0; color: white; border: none; font-weight: 500;}
        .btn-primary-custom:hover { background-color: #2c5282; color: white; }
        .table { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.03); margin-bottom: 0;}
        .table thead th { background-color: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 15px;}
        .table tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .action-btn { font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; padding: 5px 12px;}
    </style>
</head>
<body>
    <nav class="navbar navbar-dark shadow-sm py-3 mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-uppercase m-0 d-flex align-items-center" href="{{ route('liturgy.gallery') }}">
                <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" alt="Logo GPI Papua" height="30" class="me-3">
                <span class="fw-bold" style="font-size: 1.1rem; letter-spacing: 1px;">Database Lagu</span>
            </a>
            <div>
                <a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-light btn-sm px-4 fw-medium me-2">Kembali ke Galeri</a>
                <a href="{{ route('songs.create') }}" class="btn btn-primary-custom btn-sm px-4 fw-bold">&plus; Tambah Lagu Baru</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        @if(session('success')) 
            <div class="alert alert-success border-0 border-start border-4 border-success bg-white shadow-sm mb-4">{{ session('success') }}</div> 
        @endif

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 d-flex justify-content-between align-items-center bg-white border-bottom">
                <h5 class="mb-0 fw-bold text-dark">Daftar Lagu Tersimpan</h5>
                <form action="{{ route('songs.index') }}" method="GET" class="d-flex" style="width: 300px;">
                    <input type="text" name="search" class="form-control form-control-sm me-2 bg-light" placeholder="Cari Judul / Nomor..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-secondary fw-medium px-3">Cari</button>
                </form>
            </div>
            
            <div class="table-responsive p-0">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">Buku & Nomor</th>
                            <th>Judul Lagu</th>
                            <th>Jumlah Bait</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($songs as $song)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-secondary text-white fw-bold px-2 py-1">{{ $song->book ?: 'BEBAS' }}</span>
                                <span class="fw-bold text-dark ms-2">{{ $song->number ?: '-' }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary" style="font-size: 0.95rem;">{{ $song->title }}</div>
                                @if($song->chorus) <div class="small text-info fw-medium mt-1">Lagu ini memiliki Reff/Chorus</div> @endif
                            </td>
                            <td class="text-muted fw-medium">{{ count($song->verses) }} Bait</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('songs.print', $song->id) }}" target="_blank" class="btn btn-light border btn-sm text-success action-btn me-1">Cetak PDF</a>
                                <a href="{{ route('songs.edit', $song->id) }}" class="btn btn-light border btn-sm text-primary action-btn me-1">Edit</a>
                                <form action="{{ route('songs.destroy', $song->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lagu ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-light border btn-sm text-danger action-btn">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center p-5 text-muted">
                                <i>Tidak ada lagu yang ditemukan dalam database.</i>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $songs->links() }}
        </div>
    </div>
</body>
</html>