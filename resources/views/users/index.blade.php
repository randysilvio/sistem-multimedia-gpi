@extends('layouts.admin')

@section('eyebrow', 'Sistem')
@section('title', 'Pengguna')

@section('content')
<div class="row g-4">
    <div class="col-xl-8 min-w-0">
        <div class="adm-card">
            <div class="adm-card-head">
                <h2>Akun operator</h2>
                <span class="adm-muted small">{{ $users->count() }} akun</span>
            </div>
            <div class="table-responsive">
                <table class="adm-table adm-table-stack">
                    <thead>
                        <tr><th>Nama</th><th>Email</th><th>Dibuat</th><th class="text-end">Aksi</th></tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td data-label="Nama">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="adm-user-avatar" style="width:30px;height:30px;font-size:13px" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                    <strong>{{ $user->name }}</strong>
                                    @if($user->is(auth()->user()))<span class="adm-badge is-blue">Anda</span>@endif
                                </div>
                            </td>
                            <td data-label="Email" class="text-break">{{ $user->email }}</td>
                            <td data-label="Dibuat" class="adm-muted">{{ optional($user->created_at)->translatedFormat('d M Y') }}</td>
                            <td class="adm-td-actions">
                                <div class="adm-row-actions">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-soft is-primary">Ubah</a>
                                    @unless($user->is(auth()->user()))
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm="Hapus akun {{ $user->name }}? Orang ini tidak akan bisa masuk lagi.">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft is-danger">Hapus</button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="adm-card">
            <div class="adm-card-head"><h2>Tambah akun</h2></div>
            <form method="POST" action="{{ route('users.store') }}" class="adm-card-body" autocomplete="off">
                @csrf
                @if($errors->create->any())
                    <div class="adm-flash adm-flash-error py-2"><div>@foreach($errors->create->all() as $e)<div>{{ $e }}</div>@endforeach</div></div>
                @endif
                <div class="mb-3">
                    <label class="form-label" for="u-name">Nama</label>
                    <input id="u-name" name="name" class="form-control" value="{{ old('name') }}" required maxlength="255" placeholder="Nama operator">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="u-email">Email (untuk masuk)</label>
                    <input id="u-email" type="email" name="email" class="form-control" value="{{ old('email') }}" required maxlength="255" placeholder="nama@contoh.com">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="u-pass">Kata sandi</label>
                    <input id="u-pass" type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                    <div class="form-text">Minimal 8 karakter.</div>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="u-pass2">Ulangi kata sandi</label>
                    <input id="u-pass2" type="password" name="password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Buat akun</button>
            </form>
        </div>
        <div class="info-tip mt-3 mb-0">
            <div>Pendaftaran umum ditutup demi keamanan. Hanya pengguna yang sudah masuk yang dapat membuat akun baru.</div>
        </div>
    </div>
</div>
@endsection
