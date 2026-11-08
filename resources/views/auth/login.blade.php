@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <h1>Masuk</h1>
    <p class="adm-muted mb-4">Gunakan akun operator multimedia.</p>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="form-control form-control-lg @error('email') is-invalid @enderror" placeholder="nama@contoh.com">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-baseline">
                <label class="form-label" for="password">Kata sandi</label>
                @if (Route::has('password.request'))
                    <a class="small fw-semibold text-decoration-none" href="{{ route('password.request') }}">Lupa kata sandi?</a>
                @endif
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="form-control form-control-lg @error('password') is-invalid @enderror">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
            <label class="form-check-label" for="remember_me">Ingat saya di perangkat ini</label>
        </div>
        <button type="submit" class="btn btn-primary w-100">Masuk</button>
    </form>

    <p class="adm-muted small mt-4 mb-0">Belum punya akun? Hubungi admin multimedia.</p>
@endsection
