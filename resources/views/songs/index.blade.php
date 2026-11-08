@extends('layouts.admin')

@section('eyebrow', 'Data')
@section('title', 'Database Lagu')

@section('actions')
<a href="{{ route('songs.create') }}" class="btn btn-primary">Tambah Lagu</a>
@endsection

@section('content')
<div class="adm-card">
    <div class="adm-card-head">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('songs.index', array_filter(['search' => $search])) }}" @class(['btn btn-sm', 'btn-primary' => $book === '', 'btn-soft' => $book !== ''])>Semua</a>
            @foreach($books as $b)
                <a href="{{ route('songs.index', array_filter(['buku' => $b, 'search' => $search])) }}" @class(['btn btn-sm', 'btn-primary' => $book === $b, 'btn-soft' => $book !== $b])>{{ $b }}</a>
            @endforeach
        </div>
        <form action="{{ route('songs.index') }}" method="GET" class="d-flex gap-2" role="search" style="min-width: min(100%, 320px)">
            @if($book !== '')<input type="hidden" name="buku" value="{{ $book }}">@endif
            <input type="search" name="search" class="form-control form-control-sm" placeholder="Judul, lirik, atau mis. KJ 21" value="{{ $search }}" aria-label="Cari lagu">
            <button type="submit" class="btn btn-sm btn-soft" data-no-lock >Cari</button>
        </form>
    </div>

    @if($songs->isEmpty())
        <div class="adm-empty">
            <strong>Tidak ada lagu yang cocok</strong>
            @if($search !== '' || $book !== '')
                Coba kata kunci lain atau <a href="{{ route('songs.index') }}">tampilkan semua lagu</a>.
            @else
                Tambahkan lagu pertama dengan tombol <em>Tambah Lagu</em>.
            @endif
        </div>
    @else
        <div class="table-responsive">
            <table class="adm-table adm-table-stack">
                <thead>
                    <tr><th style="width:130px">Buku &amp; nomor</th><th>Judul</th><th>Isi</th><th class="text-end">Aksi</th></tr>
                </thead>
                <tbody>
                @foreach($songs as $song)
                    <tr>
                        <td>
                            <span class="adm-badge is-blue">{{ $song->book ?: 'LAIN' }}</span>
                            <strong class="ms-1">{{ $song->number ?: '-' }}</strong>
                        </td>
                        <td data-label="Judul">
                            <a href="{{ route('songs.edit', $song->id) }}" class="fw-semibold text-decoration-none">{{ $song->title }}</a>
                        </td>
                        <td data-label="Isi" class="adm-muted">
                            {{ count($song->verses ?? []) }} bait
                            @if($song->chorus)<span class="adm-badge is-orange ms-1">Reff</span>@endif
                            @if($song->is_incomplete)<span class="adm-badge is-warn ms-1" title="Lirik belum lengkap">belum lengkap</span>@endif
                        </td>
                        <td class="adm-td-actions">
                            <div class="adm-row-actions">
                                <a href="{{ route('songs.print', $song->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-soft">Cetak</a>
                                <a href="{{ route('songs.edit', $song->id) }}" class="btn btn-sm btn-soft is-primary">Edit</a>
                                <form action="{{ route('songs.destroy', $song->id) }}" method="POST" data-confirm="Hapus lagu {{ $song->book }} {{ $song->number }} - {{ $song->title }}?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-soft is-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($songs->hasPages())
            <div class="p-3 border-top">{{ $songs->links() }}</div>
        @endif
    @endif
</div>
@endsection
