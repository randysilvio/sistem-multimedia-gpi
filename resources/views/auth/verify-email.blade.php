@extends('layouts.auth')

@section('title', 'Verifikasi email')

@section('content')
    <h1>Verifikasi email</h1>
    <p class="adm-muted mb-4">Buka tautan verifikasi yang dikirim ke email akun.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="adm-flash adm-flash-success"><span>Tautan verifikasi baru sudah dikirim.</span></div>
    @endif

    <div class="d-flex gap-2 flex-wrap">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-grow-1">
            @csrf
            <button type="submit" class="btn btn-primary w-100">Kirim ulang email verifikasi</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Keluar</button>
        </form>
    </div>
@endsection
