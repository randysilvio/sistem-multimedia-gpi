@extends('layouts.admin')

@section('eyebrow', 'Shalom, ' . (Auth::user()->name ?? 'Admin'))
@section('title', 'Dashboard')

@section('actions')
    <a href="{{ route('liturgy.gallery') }}" class="btn btn-primary">Jadwal Baru</a>
@endsection

@section('content')
<div class="row g-4">
    {{-- Ibadah berikutnya --}}
    <div class="col-12">
        <div class="adm-hero">
            <img src="{{ $logoUrl }}" alt="" class="adm-hero-logo" width="150" height="150" onerror="this.style.display='none'">
            @if($nextSchedule)
                <span class="adm-pill">Ibadah berikutnya &middot; {{ $nextSchedule->worship_date->isToday() ? 'Hari ini' : ($nextSchedule->worship_date->isTomorrow() ? 'Besok' : now()->startOfDay()->diffInDays($nextSchedule->worship_date->copy()->startOfDay()) . ' hari lagi') }}</span>
                <h2>{{ $nextSchedule->theme ?: str_replace(' (Custom)', '', $nextSchedule->liturgy->name ?? 'Ibadah') }}</h2>
                <div class="adm-hero-meta mb-3">
                    {{ $nextSchedule->worship_date->locale('id')->translatedFormat('l, d F Y') }}
                    &middot; {{ str_replace(' (Custom)', '', $nextSchedule->liturgy->name ?? 'Ibadah') }}
                    @if($nextSchedule->preacher_name) &middot; Pelayan firman: {{ $nextSchedule->preacher_name }} @endif
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('liturgy.control', $nextSchedule) }}" class="btn btn-light">Buka Control Panel</a>
                    <a href="{{ route('liturgy.edit', $nextSchedule) }}" class="btn btn-outline-light">Edit Isi</a>
                    <a href="{{ route('liturgy.control', [$nextSchedule, 'uji' => 1]) }}" class="btn btn-outline-light" title="Periksa semua slide: huruf terlalu kecil, slide kosong">Uji Tayang</a>
                </div>
            @else
                <span class="adm-pill">Ibadah berikutnya</span>
                <h2>Belum ada jadwal mendatang</h2>
                <div class="adm-hero-meta mb-3">Buat jadwal dari template liturgi atau rakit presentasi kustom.</div>
                <a href="{{ route('liturgy.gallery') }}" class="btn btn-light">Buat Jadwal</a>
            @endif
        </div>
    </div>

    @if($nextSchedule && $readiness)
    {{-- Cek kesiapan ibadah berikutnya --}}
    <div class="col-12">
        <div class="adm-card">
            <div class="adm-card-head">
                <h2>Cek kesiapan ibadah</h2>
                <span class="adm-muted small">{{ $nextSchedule->worship_date->locale('id')->translatedFormat('l, d F Y') }}</span>
            </div>
            <ul class="adm-checklist">
                @foreach($readiness as $row)
                    <li class="is-{{ $row['level'] }}">
                        <span>{{ $row['text'] }}</span>
                        @if($row['url'])<a href="{{ $row['url'] }}" class="adm-link-btn">Perbaiki</a>@endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Statistik --}}
    @php
        $statCards = [
            ['Jadwal ibadah', $stats['schedules'], route('liturgy.gallery')],
            ['Template liturgi', $stats['templates'], route('liturgy.gallery')],
            ['Lagu tersimpan', $stats['songs'], route('songs.index')],
            ['Warta aktif', $stats['announcements'], route('announcement.index')],
        ];
    @endphp
    @foreach($statCards as [$label, $value, $url])
        <div class="col-6 col-xl-3">
            <a href="{{ $url }}" class="adm-card adm-stat text-decoration-none text-reset h-100">
                <span>
                    <span class="adm-stat-value d-block">{{ number_format($value, 0, ',', '.') }}</span>
                    <span class="adm-stat-label">{{ $label }}</span>
                </span>
            </a>
        </div>
    @endforeach

    {{-- Jadwal terbaru --}}
    <div class="col-lg-7">
        <div class="adm-card h-100">
            <div class="adm-card-head">
                <h2>Jadwal terbaru</h2>
                <a href="{{ route('liturgy.gallery') }}" class="small fw-semibold text-decoration-none">Lihat semua</a>
            </div>
            @if($recentSchedules->isEmpty())
                <div class="adm-card-body adm-muted">Belum ada jadwal ibadah.</div>
            @else
                <ul class="adm-list">
                    @foreach($recentSchedules as $s)
                        <li>
                            <span class="adm-date"><b>{{ $s->worship_date->format('d') }}</b><span>{{ $s->worship_date->locale('id')->translatedFormat('M') }}</span></span>
                            <span class="flex-grow-1 min-w-0">
                                <strong class="d-block text-truncate">{{ $s->theme ?: str_replace(' (Custom)', '', $s->liturgy->name ?? 'Ibadah') }}</strong>
                                <span class="small adm-muted text-truncate d-block">{{ str_replace(' (Custom)', '', $s->liturgy->name ?? 'Ibadah') }}@if($s->preacher_name) &middot; {{ $s->preacher_name }}@endif</span>
                            </span>
                            <a href="{{ route('liturgy.edit', $s) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <a href="{{ route('liturgy.control', $s) }}" class="btn btn-sm btn-primary">Live</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Akses cepat + logo --}}
    <div class="col-lg-5">
        <div class="d-grid gap-3">
            <a href="{{ route('liturgy.builder') }}" class="adm-quick">
                <span><strong>Presentasi kustom</strong><span>Rakit slide sekali pakai tanpa template</span></span>
            </a>
            <a href="{{ route('songs.create') }}" class="adm-quick">
                <span><strong>Tambah lagu</strong><span>Simpan lirik agar bisa ditarik ke slide</span></span>
            </a>
            <a href="{{ route('announcement.index') }}" class="adm-quick">
                <span><strong>Unggah warta sinode</strong><span>Gambar 1920×1080 tampil sebelum ibadah</span></span>
            </a>
            <a href="{{ route('settings.edit') }}" class="adm-quick">
                <span>
                    <strong>{{ $hasUploadedLogo ? 'Ganti logo gereja' : 'Unggah logo gereja' }}</strong>
                    <span>{{ $hasUploadedLogo ? 'Logo unggahan sedang dipakai di semua layar' : 'Belum ada logo unggahan, memakai logo bawaan' }}</span>
                </span>
            </a>
        </div>
    </div>
</div>
@endsection
