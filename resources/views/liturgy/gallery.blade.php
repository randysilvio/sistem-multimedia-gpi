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
        
        /* Gaya untuk area template card scroll menyamping */
        .template-scroll-container {
            display: flex;
            gap: 1.25rem;
            overflow-x: auto;
            padding-bottom: 15px;
            scrollbar-width: thin;
        }
        .template-scroll-container::-webkit-scrollbar {
            height: 6px;
        }
        .template-scroll-container::-webkit-scrollbar-thumb {
            background-color: #cbd5e0;
            border-radius: 10px;
        }

        .template-card { 
            flex: 0 0 160px; /* Lebar card tetap 160px */
            height: 210px; 
            border-radius: 10px; 
            border: 1px solid #e2e8f0; 
            background: #ffffff; 
            cursor: pointer; 
            transition: all 0.2s ease; 
            position: relative; 
            padding: 12px; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            text-decoration: none !important;
        }
        .template-card:hover { 
            border-color: #3182ce; 
            box-shadow: 0 8px 20px rgba(49,130,206,0.08); 
            transform: translateY(-3px); 
        }
        .card-blank { font-size: 3.5rem; color: #cbd5e0; font-weight: 300; margin-bottom: 5px; }
        
        /* Desain garis abstrak pada template card */
        .mockup-container { width: 100%; display: flex; flex-direction: column; gap: 6px; margin-bottom: auto; opacity: 0.7;}
        .line-mockup { background: #edf2f7; height: 6px; border-radius: 3px; width: 100%; }
        .line-mockup.title { height: 10px; width: 75%; background: #3182ce; opacity: 0.5; margin-bottom: 10px; }
        .line-mockup.short { width: 60%; }
        
        .template-name {
            font-size: 0.8rem;
            font-weight: 600;
            color: #4a5568;
            text-align: center;
            line-height: 1.3;
            margin-top: auto;
        }

        .table { font-size: 0.9rem; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .table thead th { background-color: #f8fafc; color: #64748b; font-weight: 600; border-bottom: 1px solid #e2e8f0; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding-top: 12px; padding-bottom: 12px;}
        .btn-control { background-color: #2b6cb0; color: white; border: none; font-weight: 500;}
        .btn-control:hover { background-color: #2c5282; color: white; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-uppercase m-0 d-flex align-items-center" href="{{ route('dashboard') }}">
                <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" alt="Logo GPI" height="32" class="me-3">
                <span>Sistem Multimedia</span>
            </a>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm fw-medium px-3 me-2">Ke Dashboard</a>
                <a href="{{ route('songs.index') }}" class="btn btn-light btn-sm fw-bold px-4 text-primary">DATABASE LAGU</a>
            </div>
        </div>
    </nav>

    <div class="bg-white py-4 border-bottom shadow-sm mb-5">
        <div class="container">
            <h6 class="text-secondary mb-3 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Mulai Presentasi Baru (Pilih Template)</h6>
            
            <div class="template-scroll-container">
                <a href="{{ route('liturgy.builder') }}" class="template-card">
                    <div class="card-blank">&plus;</div>
                    <span class="template-name">Custom Builder (Kosong)</span>
                </a>

                @if(isset($liturgies))
                    @foreach($liturgies as $liturgy)
                        <a href="{{ route('liturgy.create', ['liturgy_id' => $liturgy->id]) }}" class="template-card">
                            <div class="mockup-container mt-2">
                                <div class="line-mockup title"></div>
                                <div class="line-mockup"></div>
                                <div class="line-mockup"></div>
                                <div class="line-mockup short"></div>
                            </div>
                            <span class="template-name">{{ $liturgy->name }}</span>
                        </a>
                    @endforeach
                @endif
            </div>
            
        </div>
    </div>

    <div class="container pb-5">
        @if(session('success')) 
            <div class="alert alert-success shadow-sm border-0 border-start border-4 border-success">{{ session('success') }}</div> 
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px; color: #64748b;">Riwayat Presentasi Ibadah</h6>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                @if($schedules->isEmpty())
                    <div class="text-center p-5 text-muted">
                        <i>Belum ada presentasi yang tersimpan. Silakan pilih template di atas untuk memulai.</i>
                    </div>
                @else
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Hari / Tanggal</th>
                                <th>Jenis Tata Ibadah</th>
                                <th>Pelayan Firman</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $schedule)
                                <tr>
                                    <td class="ps-4 align-middle">
                                        <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($schedule->worship_date)->translatedFormat('l') }}</div>
                                        <div class="small text-muted">{{ \Carbon\Carbon::parse($schedule->worship_date)->translatedFormat('d F Y') }}</div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <div style="width: 12px; height: 12px; background-color: {{ $schedule->theme_color ?? '#cbd5e0' }}; border-radius: 50%; margin-right: 12px;"></div>
                                            <span class="fw-medium text-secondary">{{ $schedule->liturgy->name ?? $schedule->theme }}</span>
                                        </div>
                                    </td>
                                    <td class="align-middle text-muted">{{ $schedule->preacher_name ?? '-' }}</td>
                                    <td class="text-end pe-4 align-middle">
                                        <a href="{{ route('liturgy.edit', $schedule->id) }}" class="btn btn-control btn-sm fw-bold px-3 me-1">Live Control</a>
                                        <form action="{{ route('liturgy.destroy', $schedule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data presentasi ini secara permanen?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-light border btn-sm text-danger">&times;</button>
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