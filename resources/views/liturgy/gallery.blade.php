<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Presentasi - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> 
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; color: #1e293b; } 
        
        .navbar { background-color: #0f172a !important; }
        .navbar-brand { font-weight: 700; letter-spacing: 1px; font-size: 1.1rem; }
        .section-label { font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #64748b; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .section-label::after { content: ""; flex: 1; height: 1px; background-color: #e2e8f0; }
        
        /* SCROLL CONTAINER */
        .template-scroll-container { display: flex; gap: 1.5rem; overflow-x: auto; padding-bottom: 20px; padding-top: 5px; scrollbar-width: thin; }
        .template-scroll-container::-webkit-scrollbar { height: 8px; }
        .template-scroll-container::-webkit-scrollbar-track { background: transparent; }
        .template-scroll-container::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }

        /* KARTU TEMPLATE ELEGAN */
        .template-card { 
            flex: 0 0 220px; 
            height: 160px; 
            border-radius: 12px; 
            border: 1px solid #e2e8f0; 
            background: #ffffff; 
            cursor: pointer; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            position: relative; 
            display: flex; 
            flex-direction: column; 
            text-decoration: none !important; 
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }
        .template-card:hover { 
            border-color: #3b82f6; 
            box-shadow: 0 15px 25px -5px rgba(59, 130, 246, 0.15); 
            transform: translateY(-4px); 
        }

        /* WATERMARK LOGO */
        .watermark-logo {
            position: absolute;
            right: -25px;
            bottom: -25px;
            height: 120px;
            opacity: 0.04;
            z-index: 0;
            pointer-events: none;
            filter: grayscale(100%);
            transition: transform 0.4s ease;
        }
        .template-card:hover .watermark-logo { transform: scale(1.1) rotate(-5deg); }

        .t-card-content { position: relative; z-index: 1; height: 100%; display: flex; flex-direction: column; justify-content: space-between; }
        
        .t-card-type { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; }
        .t-card-title { font-size: 1.05rem; font-weight: 800; color: #1e293b; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

        .card-action { font-size: 0.75rem; font-weight: 600; color: #3b82f6; display: flex; align-items: center; justify-content: space-between; }
        
        .delete-btn { color: #ef4444; border: none; background: transparent; padding: 0; font-size: 0.75rem; font-weight: 600; cursor: pointer; z-index: 10; position: relative; }
        .delete-btn:hover { text-decoration: underline; }

        /* KARTU SPESIAL */
        .template-card.create-new { background: #f8fafc; border: 2px dashed #cbd5e1; }
        .template-card.create-new:hover { background: #eff6ff; border-color: #3b82f6; }
        .template-card.create-new .t-card-title { color: #3b82f6; }
        
        .template-card.create-template { background: #f0fdf4; border: 2px dashed #a7f3d0; }
        .template-card.create-template:hover { background: #ecfdf5; border-color: #10b981; }
        .template-card.create-template .t-card-title { color: #10b981; }

        .table { font-size: 0.9rem; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .table thead th { background-color: #f1f5f9; color: #475569; font-weight: 700; border-bottom: 1px solid #e2e8f0; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding-top: 15px; padding-bottom: 15px;}
        .table tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f8fafc; }
        .btn-control { background-color: #2b6cb0; color: white; border: none; font-weight: 600; }
        .btn-control:hover { background-color: #1e40af; color: white; }
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
                <a href="{{ route('songs.index') }}" class="btn btn-light btn-sm fw-bold px-4 text-primary">Database Lagu</a>
            </div>
        </div>
    </nav>

    <div class="bg-white py-5 border-bottom shadow-sm mb-5">
        <div class="container">
            <h6 class="section-label">Manajemen Tata Ibadah</h6>
            
            <div class="template-scroll-container">
                
                <a href="{{ route('liturgy.builder') }}" class="template-card create-new">
                    <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" class="watermark-logo">
                    <div class="t-card-content">
                        <div class="t-card-type text-primary">Kustomisasi Langsung</div>
                        <div class="t-card-title">Presentasi Sekali Pakai</div>
                        <div class="card-action text-primary">Buat Baru &rarr;</div>
                    </div>
                </a>

                <a href="{{ route('liturgy.template.create') }}" class="template-card create-template">
                    <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" class="watermark-logo">
                    <div class="t-card-content">
                        <div class="t-card-type text-success">Database Master</div>
                        <div class="t-card-title">Buat Template Baru</div>
                        <div class="card-action text-success">Rancang Kerangka &rarr;</div>
                    </div>
                </a>

                @if(isset($liturgies))
                    @foreach($liturgies as $liturgy)
                        <div class="template-card" onclick="window.location='{{ route('liturgy.create', ['liturgy_id' => $liturgy->id]) }}'">
                            <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" class="watermark-logo">
                            <div class="t-card-content">
                                <div class="t-card-type">Template Tersimpan</div>
                                <div class="t-card-title">{{ $liturgy->name }}</div>
                                <div class="card-action">
                                    <span>Gunakan &rarr;</span>
                                    <form action="{{ route('liturgy.template.destroy', $liturgy->id) }}" method="POST" onclick="event.stopPropagation();" onsubmit="return confirm('Hapus template ini secara permanen?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="delete-btn">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            
        </div>
    </div>

    <div class="container pb-5">
        @if(session('success')) 
            <div class="alert alert-success shadow-sm border-0 border-start border-4 border-success mb-4 fw-medium">{{ session('success') }}</div> 
        @endif

        <h6 class="section-label">Riwayat Presentasi Tersimpan</h6>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-0">
                @if(isset($schedules) && $schedules->isEmpty())
                    <div class="text-center p-5 text-muted">
                        <i>Belum ada jadwal presentasi yang tersimpan. Silakan pilih template di atas untuk memulai.</i>
                    </div>
                @elseif(isset($schedules))
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Hari / Tanggal Ibadah</th>
                                <th>Jenis Tata Ibadah</th>
                                <th>Pelayan Firman</th>
                                <th class="text-end pe-4">Aksi Presentasi</th>
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
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $schedule->liturgy->name ?? 'Presentasi Kustom' }}</div>
                                                @if($schedule->theme)
                                                    <div class="small text-muted" style="font-size: 0.75rem;">{{ $schedule->theme }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle text-secondary fw-medium">{{ $schedule->preacher_name ?? '-' }}</td>
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