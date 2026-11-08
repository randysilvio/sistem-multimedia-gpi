@extends('layouts.admin')

@section('eyebrow', 'Pengguna')
@section('title', 'Ubah akun: ' . $user->name)

@section('actions')
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
<div class="adm-card" style="max-width: 640px">
    <form method="POST" action="{{ route('users.update', $user) }}" class="adm-card-body" autocomplete="off">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label" for="u-name">Nama</label>
            <input id="u-name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required maxlength="255">
        </div>
        <div class="mb-4">
            <label class="form-label" for="u-email">Email</label>
            <input id="u-email" type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required maxlength="255">
        </div>
        <div class="adm-section-label">Atur ulang kata sandi (opsional)</div>
        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <label class="form-label" for="u-pass">Kata sandi baru</label>
                <input id="u-pass" type="password" name="password" class="form-control" minlength="8" autocomplete="new-password">
            </div>
            <div class="col-sm-6">
                <label class="form-label" for="u-pass2">Ulangi kata sandi baru</label>
                <input id="u-pass2" type="password" name="password_confirmation" class="form-control" minlength="8" autocomplete="new-password">
            </div>
            <div class="col-12 form-text mt-1">Kosongkan bila kata sandi tidak diganti.</div>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection
