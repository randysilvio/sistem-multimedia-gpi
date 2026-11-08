@extends('layouts.admin')

@section('eyebrow', 'Buat dari Buletin')
@section('title', 'Periksa Hasil Bacaan')

@section('content')
    @php $liturgy = $result['liturgy']; @endphp

    {{-- Ganti template lalu baca ulang --}}
    <form method="POST" action="{{ route('liturgy.import.review') }}" class="adm-card mb-4">
        @csrf
        <input type="hidden" name="text" value="{{ $text }}">
        <div class="p-3 d-flex flex-wrap align-items-end gap-3">
            <div class="flex-grow-1" style="min-width: 240px">
                <label class="form-label small fw-semibold mb-1" for="re-liturgy">Template terdeteksi</label>
                <select name="liturgy_id" id="re-liturgy" class="form-select">
                    @foreach($liturgies as $l)
                        <option value="{{ $l->id }}" @selected($liturgy && $liturgy->id === $l->id)>{{ $l->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-outline-secondary">Baca ulang dengan template ini</button>
            <a href="{{ route('liturgy.import') }}" class="btn btn-soft">Kembali ke teks</a>
        </div>
    </form>

    @if(!$liturgy)
        <div class="adm-flash adm-flash-error"><span>Belum ada template. Buat template dulu.</span></div>
    @else
    <form method="POST" action="{{ route('liturgy.import.store') }}">
        @csrf
        <input type="hidden" name="liturgy_id" value="{{ $liturgy->id }}">

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="adm-card h-100">
                    <div class="adm-card-head"><h2>Keterangan ibadah</h2></div>
                    <div class="p-3 p-md-4">
                        <label class="form-label small fw-semibold" for="worship_date">Tanggal ibadah</label>
                        <input type="date" name="worship_date" id="worship_date" class="form-control mb-3" value="{{ $date }}" required>
                        @if(!$result['date'])<p class="small text-warning-emphasis mt-n2">Tanggal tidak ditemukan, diisi hari Minggu terdekat.</p>@endif

                        <label class="form-label small fw-semibold" for="theme">Tema</label>
                        <input type="text" name="theme" id="theme" class="form-control mb-3" value="{{ $result['theme'] }}" placeholder="(opsional)">

                        <label class="form-label small fw-semibold" for="preacher_name">Pelayan Firman</label>
                        <input type="text" name="preacher_name" id="preacher_name" class="form-control mb-3" placeholder="mis. Pdt. …">

                        <label class="form-label small fw-semibold" for="bible">Bacaan Alkitab (TB)</label>
                        <input type="text" name="bible" id="bible" class="form-control" value="{{ $result['bible'] }}" placeholder="mis. Efesus 1:15-23">
                        @if(!$result['bible'])<p class="small text-warning-emphasis mt-1">Bacaan tidak ditemukan. Silakan isi sendiri.</p>@endif
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="adm-card">
                    <div class="adm-card-head">
                        <h2>Lagu: {{ $liturgy->name }}</h2>
                        <span class="adm-muted small">Bait kosong = semua bait</span>
                    </div>
                    <div class="table-responsive">
                        <table class="adm-table adm-table-stack">
                            <thead><tr><th>Slot</th><th style="width:110px">Buku</th><th style="width:130px">Nomor</th><th style="width:110px">Bait</th><th>Lagu di database</th></tr></thead>
                            <tbody>
                            @foreach($result['slots'] as $slot)
                                @php $pick = $slot['pick']; $song = $slot['song']; @endphp
                                <tr>
                                    <td data-label="Slot" class="fw-semibold">{{ $slot['title'] }}</td>
                                    <td data-label="Buku">
                                        <select name="songs[{{ $slot['item_id'] }}][book]" class="form-select form-select-sm">
                                            @foreach(['KJ', 'NKB', 'PKJ', 'NR', 'DSL', 'GPM', 'BEBAS'] as $b)
                                                <option value="{{ $b }}" @selected(($pick['book'] ?? '') === $b)>{{ $b === 'BEBAS' ? 'Lainnya' : $b }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td data-label="Nomor"><input name="songs[{{ $slot['item_id'] }}][number]" class="form-control form-control-sm" value="{{ $pick['number'] ?? '' }}" placeholder="kosong = lewati"></td>
                                    <td data-label="Bait"><input name="songs[{{ $slot['item_id'] }}][verses]" class="form-control form-control-sm" value="{{ isset($pick['verses']) && $pick['verses'] ? implode(', ', $pick['verses']) : '' }}" placeholder="semua"></td>
                                    <td data-label="Di database">
                                        @if($song)
                                            <span class="small">{{ $song->displayTitle() }}</span>
                                            @if($song->is_incomplete)<span class="adm-badge is-warn ms-1" title="Lirik di database belum lengkap">lirik belum lengkap</span>@endif
                                        @elseif($pick)
                                            <span class="adm-badge is-danger">tidak ada di database</span>
                                        @else
                                            <span class="adm-muted small">tidak ditemukan di buletin</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($result['unused'] || $result['templateSongs'])
                        <div class="p-3 border-top small">
                            @if($result['unused'])<div class="mb-1"><b>Disebut di buletin tapi tidak dipakai:</b> {{ implode(' · ', $result['unused']) }}</div>@endif
                            @if($result['templateSongs'])<div class="adm-muted">Sudah tertulis di template (tidak perlu slot): {{ implode(' · ', $result['templateSongs']) }}</div>@endif
                        </div>
                    @endif
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('liturgy.import') }}" class="btn btn-soft">Batal</a>
                    <button type="submit" class="btn btn-primary">Buat jadwal</button>
                </div>
            </div>
        </div>
    </form>
    @endif
@endsection
