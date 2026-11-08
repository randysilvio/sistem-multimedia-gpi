@extends('layouts.admin')

@section('eyebrow', 'Pengelolaan')
@section('title', 'Riwayat Perubahan')

@section('content')
    <div class="adm-card">
        <div class="adm-card-head">
            <h2>Siapa mengubah apa</h2>
            <span class="adm-muted small">{{ $logs->total() }} catatan</span>
        </div>
        @if($logs->isEmpty())
            <div class="adm-empty">
                <strong>Belum ada riwayat</strong>
                Perubahan jadwal, lagu, template, pengguna dan pengaturan akan tercatat di sini.
            </div>
        @else
            <div class="table-responsive">
                <table class="adm-table adm-table-stack">
                    <thead><tr><th>Waktu</th><th>Pengguna</th><th>Perubahan</th></tr></thead>
                    <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td data-label="Waktu" class="text-nowrap">
                                <div class="fw-semibold">{{ $log->created_at->timezone(config('app.timezone'))->translatedFormat('d M Y') }}</div>
                                <div class="small adm-muted">{{ $log->created_at->timezone(config('app.timezone'))->format('H:i') }}</div>
                            </td>
                            <td data-label="Pengguna">{{ $log->user->name ?? 'Sistem' }}</td>
                            <td data-label="Perubahan">
                                <span class="adm-badge">{{ \Illuminate\Support\Str::of($log->action)->before('.')->title() }}</span>
                                {{ $log->description }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="p-3 border-top">{{ $logs->links() }}</div>
            @endif
        @endif
    </div>
@endsection
