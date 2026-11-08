@extends('layouts.auth')

@section('title', 'Konfirmasi kata sandi')

@section('content')
    <h1>Konfirmasi kata sandi</h1>
    <p class="adm-muted mb-4">Masukkan kata sandi untuk melanjutkan.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label" for="password">Kata sandi</label>
            <input id="password" type="password" name="password" required autofocus autocomplete="current-password"
                   class="form-control form-control-lg @error('password') is-invalid @enderror">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">Lanjutkan</button>
    </form>
@endsection
