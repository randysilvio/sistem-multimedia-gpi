@extends('layouts.admin')

@section('eyebrow', 'Sistem')
@section('title', 'Profil Saya')

@section('content')
<div class="row g-4" style="max-width: 1100px">
    <div class="col-lg-6">
        <div class="adm-card h-100">
            <div class="adm-card-head"><h2>Informasi akun</h2></div>
            <form method="POST" action="{{ route('profile.update') }}" class="adm-card-body">
                @csrf @method('PATCH')
                <div class="mb-3">
                    <label class="form-label" for="name">Nama</label>
                    <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autocomplete="name">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label" for="email">Email</label>
                    <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="adm-card h-100">
            <div class="adm-card-head"><h2>Ganti kata sandi</h2></div>
            <form method="POST" action="{{ route('password.update') }}" class="adm-card-body">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="current_password">Kata sandi saat ini</label>
                    <input id="current_password" type="password" name="current_password" autocomplete="current-password"
                           class="form-control @error('current_password', 'updatePassword') is-invalid @enderror">
                    @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label" for="password">Kata sandi baru</label>
                        <input id="password" type="password" name="password" autocomplete="new-password"
                               class="form-control @error('password', 'updatePassword') is-invalid @enderror">
                        @error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="password_confirmation">Ulangi kata sandi baru</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                               class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror">
                        @error('password_confirmation', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Ganti kata sandi</button>
            </form>
        </div>
    </div>

    <div class="col-12">
        <div class="adm-card" style="border-color:#f3c9c9 !important">
            <div class="adm-card-head"><h2 class="text-danger">Hapus akun saya</h2></div>
            <form method="POST" action="{{ route('profile.destroy') }}" class="adm-card-body d-flex flex-wrap gap-3 align-items-end"
                  data-confirm="Hapus akun Anda secara permanen? Anda akan langsung keluar.">
                @csrf @method('DELETE')
                <div class="flex-grow-1" style="min-width:220px;max-width:360px">
                    <label class="form-label" for="del_password">Ketik kata sandi untuk konfirmasi</label>
                    <input id="del_password" type="password" name="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" autocomplete="current-password">
                    @error('password', 'userDeletion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-outline-danger">Hapus akun</button>
                <p class="adm-muted small w-100 mb-0">Jadwal, lagu, dan template tidak ikut terhapus. Akun terakhir tidak bisa dihapus.</p>
            </form>
        </div>
    </div>
</div>
@endsection
