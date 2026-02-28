<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Multimedia - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> 
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { background-color: #f4f6f8; font-family: 'Inter', sans-serif; color: #2d3748; } 
        .navbar { background-color: #1a202c !important; }
        .navbar-brand { font-weight: 600; letter-spacing: 1px; font-size: 1.1rem; }
        
        .btn-control { background-color: #2b6cb0; color: white; border: none; }
        .btn-control:hover { background-color: #2c5282; color: white; }
        
        .template-card { 
            width: 160px; height: 200px; border-radius: 6px; border: 1px solid #e2e8f0; 
            background: #ffffff; cursor: pointer; transition: all 0.2s ease; position: relative;
        }
        .template-card:hover { border-color: #cbd5e0; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transform: translateY(-2px); }
        .card-blank { display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #718096; font-weight: 300; }
        .card-template-img { padding: 15px; height: 100%; display: flex; flex-direction: column; gap: 6px; opacity: 0.6; }
        .line-mockup { background: #e2e8f0; height: 6px; border-radius: 3px; width: 100%; }
        .line-mockup.short { width: 50%; }
        .line-mockup.title { height: 10px; width: 75%; background: #4a5568; margin-bottom: 12px; }
        
        .table { font-size: 0.9rem; }
        .table thead th { background-color: #edf2f7; color: #4a5568; font-weight: 600; border-bottom: 1px solid #e2e8f0; letter-spacing: 0.5px; }
        .table tbody td { vertical-align: middle; border-bottom: 1px solid #edf2f7; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-uppercase m-0" href="#">Sistem Multimedia GPI Papua</a>
            <div>
                <a href="{{ route('songs.index') }}" class="btn btn-light btn-sm fw-bold px-4 text-primary" style="letter-spacing: 0.5px;">DATABASE LAGU</a>
            </div>
        </div>
    </nav>

    <div class="bg-white py-4 border-bottom shadow-sm mb-5">
        <div class="container">
            <h6 class="text-secondary mb-3 fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Mulai Presentasi Baru</h6>
            <div class="d-flex gap-4 overflow-auto pb-3 pt-1">
                <a href="{{ route('liturgy.builder') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center">
                    <div class="template-card card-blank mb-2 shadow-sm">&plus;</div>
                    <span class="fw-medium text-center" style="font-size: 0.85rem;">Presentasi Kosong</span>
                </a>

                @if(isset($liturgies))
                    @foreach($liturgies as $liturgy)
                        <a href="{{ route('liturgy.create', ['liturgy_id' => $liturgy->id]) }}" class="text-decoration-none text-dark d-flex flex-column align-items-center">
                            <div class="template-card mb-2 shadow-sm">
                                <div class="card-template-img">
                                    <div class="line-mockup title"></div>
                                    <div class="line-mockup"></div>
                                    <div class="line-mockup short"></div>
                                    <br>
                                    <div class="line-mockup"></div>
                                    <div class="line-mockup"></div>
                                </div>
                            </div>
                            <span class="fw-medium text-center text-truncate" style="font-size: 0.85rem; width: 160px; color: #4a5568;">{{ $liturgy->name }}</span>
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <div class="container pb-5">
        @if(session('success')) 
            <div class="alert alert-success shadow-sm fw-medium border-0 bg-white border-start border-4 border-success">{{ session('success') }}</div> 
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px; color: #2d3748;">Presentasi Terakhir</h6>
        </div>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-0">
                @if($schedules->isEmpty())
                    <div class="text-center p-5">
                        <h6 class="text-muted mb-3 fw-normal">Belum ada presentasi yang tersimpan.</h6>
                    </div>
                @else
                    <table class="table table-hover mb-0">
                        <thead class="text-uppercase">
                            <tr>
                                <th class="ps-4 py-3">Tanggal Ibadah</th>
                                <th class="py-3">Tata Ibadah / Judul</th>
                                <th class="py-3">Pelayan Firman</th>
                                <th class="text-end pe-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $schedule)
                                <tr>
                                    <td class="ps-4 fw-medium text-dark">{{ \Carbon\Carbon::parse($schedule->worship_date)->translatedFormat('l, d M Y') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div style="width: 10px; height: 10px; background-color: {{ $schedule->theme_color ?? '#cbd5e0' }}; border-radius: 50%; margin-right: 12px;"></div>
                                            <span class="fw-medium text-secondary">{{ $schedule->liturgy->name ?? $schedule->theme }}</span>
                                        </div>
                                    </td>
                                    <td class="text-secondary">{{ $schedule->preacher_name ?? '-' }}</td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('liturgy.edit', $schedule->id) }}" class="btn btn-control btn-sm fw-medium px-3 me-1">Buka Live Control</a>
                                        <form action="{{ route('liturgy.destroy', $schedule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus presentasi ini secara permanen?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-light border btn-sm fw-medium px-3 text-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</body>
</html>