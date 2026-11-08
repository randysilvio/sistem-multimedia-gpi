@extends('layouts.admin')

@section('eyebrow', 'Ibadah')
@section('title')
Jadwal &amp; Presentasi
@endsection

@section('actions')
<a href="{{ route('liturgy.template.create') }}" class="btn btn-outline-secondary">Template Baru</a>
<a href="{{ route('liturgy.builder') }}" class="btn btn-outline-secondary">Presentasi Kustom</a>
<a href="{{ route('liturgy.import') }}" class="btn btn-primary">Buat dari Buletin</a>
@endsection

@section('content')
    @php $logo = \App\Services\Branding::logoUrl(); @endphp

    <div class="adm-section-label">Mulai dari template</div>
    <div class="adm-template-grid mb-4">
        @foreach($liturgies as $liturgy)
            <div class="adm-template">
                <a class="stretched" href="{{ route('liturgy.create', ['liturgy_id' => $liturgy->id]) }}" aria-label="Gunakan template {{ $liturgy->name }}"></a>
                <img src="{{ $logo }}" class="adm-template-watermark" alt="">
                <div class="adm-template-kicker">Template tata ibadah</div>
                <div class="adm-template-title">{{ $liturgy->name }}</div>
                @if($liturgy->description)<div class="adm-template-desc">{{ $liturgy->description }}</div>@endif
                <div class="adm-template-foot">
                    <span class="use">Gunakan</span>
                    <span class="d-flex gap-3">
                        <a href="{{ route('liturgy.template.edit', $liturgy->id) }}" class="adm-link-btn">Edit</a>
                        <form action="{{ route('liturgy.template.destroy', $liturgy->id) }}" method="POST" class="m-0" data-confirm="Hapus template {{ $liturgy->name }} secara permanen?">
                            @csrf @method('DELETE')
                            <button type="submit" class="adm-link-btn is-danger">Hapus</button>
                        </form>
                    </span>
                </div>
            </div>
        @endforeach

        <a href="{{ route('liturgy.builder') }}" class="adm-template is-new">
            <div class="adm-template-kicker text-primary">Sekali pakai</div>
            <div class="adm-template-title text-primary">Presentasi kustom</div>
            <div class="adm-template-desc">Susun slide bebas tanpa template, mis. ibadah khusus atau acara gereja.</div>
            <div class="adm-template-foot"><span class="use">Buat baru</span></div>
        </a>
        <a href="{{ route('liturgy.template.create') }}" class="adm-template is-new is-green">
            <div class="adm-template-kicker text-success">Database master</div>
            <div class="adm-template-title text-success">Template baru</div>
            <div class="adm-template-desc">Rancang kerangka tata ibadah yang bisa dipakai berulang setiap minggu.</div>
            <div class="adm-template-foot"><span class="use text-success">Rancang kerangka</span></div>
        </a>
    </div>

    <div class="adm-card">
        <div class="adm-card-head">
            <h2>Jadwal tersimpan</h2>
            <span class="adm-muted small">{{ $schedules->total() }} jadwal</span>
        </div>
        @if($schedules->isEmpty())
            <div class="adm-empty">
                <strong>Belum ada jadwal</strong>
                Pilih salah satu template di atas untuk menyusun jadwal ibadah pertama.
            </div>
        @else
            <div class="table-responsive">
                <table class="adm-table adm-table-stack">
                    <thead>
                        <tr><th>Tanggal ibadah</th><th>Tata ibadah</th><th>Pelayan firman</th><th class="text-end">Aksi</th></tr>
                    </thead>
                    <tbody>
                    @foreach($schedules as $schedule)
                        @php $date = \Carbon\Carbon::parse($schedule->worship_date); @endphp
                        <tr>
                            <td data-label="Tanggal">
                                <div class="fw-bold">{{ $date->translatedFormat('l') }}</div>
                                <div class="small adm-muted">{{ $date->translatedFormat('d F Y') }}</div>
                            </td>
                            <td data-label="Tata ibadah">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="adm-dot" style="background: {{ $schedule->theme_color ?? '#cbd5e0' }}"></span>
                                    <div class="min-w-0">
                                        <div class="fw-semibold">{{ $schedule->liturgy->name ?? 'Presentasi Kustom' }}</div>
                                        @if($schedule->theme)<div class="small adm-muted">{{ $schedule->theme }}</div>@endif
                                    </div>
                                </div>
                            </td>
                            <td data-label="Pelayan firman">{{ $schedule->preacher_name ?: '-' }}</td>
                            <td class="adm-td-actions">
                                <div class="adm-row-actions">
                                    <a href="{{ route('liturgy.edit', $schedule->id) }}" class="btn btn-sm btn-soft">Edit</a>
                                    <a href="{{ route('liturgy.bulletin', $schedule->id) }}" class="btn btn-sm btn-soft" target="_blank" rel="noopener" title="Cetak buletin tata ibadah (booklet)">Buletin</a>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-soft" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Salin jadwal ini ke tanggal lain">Duplikat</button>
                                        <form method="POST" action="{{ route('liturgy.duplicate', $schedule) }}" class="dropdown-menu dropdown-menu-end p-3 adm-dup-form">
                                            @csrf
                                            <label class="form-label small fw-semibold">Salin ke tanggal</label>
                                            <input type="date" name="worship_date" class="form-control form-control-sm mb-2" required value="{{ $date->copy()->addWeek()->toDateString() }}">
                                            <p class="small adm-muted mb-2">Isi, slide sisipan & tampilan ikut disalin. Ganti lagu & ayat sesudahnya.</p>
                                            <button type="submit" class="btn btn-sm btn-primary w-100">Duplikat</button>
                                        </form>
                                    </div>
                                    <a href="{{ route('liturgy.control', $schedule->id) }}" class="btn btn-sm btn-primary">Kontrol Live</a>
                                    <form action="{{ route('liturgy.destroy', $schedule->id) }}" method="POST" data-confirm="Hapus jadwal {{ $date->translatedFormat('d F Y') }} secara permanen?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft is-danger" aria-label="Hapus jadwal">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($schedules->hasPages())
                <div class="p-3 border-top">{{ $schedules->links() }}</div>
            @endif
        @endif
    </div>
@endsection
