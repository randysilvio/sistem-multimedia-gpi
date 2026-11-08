@extends('layouts.auth')

@section('title', 'Lupa kata sandi')

@section('content')
    <h1>Lupa kata sandi</h1>
    <p class="adm-muted mb-4">Tautan untuk membuat kata sandi baru akan dikirim ke email akun.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="form-control form-control-lg @error('email') is-invalid @enderror">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">Kirim tautan atur ulang</button>
    </form>

    <div class="info-tip mt-4 mb-3"><div>Email tidak sampai? Admin juga bisa mengatur ulang kata sandi Anda dari menu <strong>Pengguna</strong>.</div></div>
    <a href="{{ route('login') }}" class="small fw-semibold text-decoration-none">Kembali ke halaman masuk</a>
@endsection
