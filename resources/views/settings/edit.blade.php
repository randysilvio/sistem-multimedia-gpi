@extends('layouts.admin')

@section('eyebrow', 'Sistem')
@section('title', 'Pengaturan & Logo')

@section('content')
<form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
    @csrf
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="adm-card">
                <div class="adm-card-head">
                    <h2>Logo gereja</h2>
                    @if($hasUploadedLogo)
                        <span class="badge text-bg-success">Logo unggahan aktif</span>
                    @else
                        <span class="badge text-bg-secondary">Memakai logo bawaan</span>
                    @endif
                </div>
                <div class="adm-card-body">
                    <p class="adm-muted mb-3">Logo tampil di area atas layar live, slide pembuka &amp; penutup, control panel, dan menu aplikasi. Perubahan langsung berlaku. Layar live yang sedang terbuka cukup dimuat ulang.</p>

                    <label class="adm-dropzone mb-2" id="dropzone" for="logo">
                        <strong>Klik untuk memilih logo, atau seret file ke sini</strong>
                        <span class="small adm-muted">PNG, JPG, atau WEBP &middot; maks. 4 MB &middot; disarankan PNG transparan minimal 500×500 px</span>
                        <span class="small fw-semibold text-primary" id="fileName"></span>
                    </label>
                    <input type="file" name="logo" id="logo" class="d-none" accept="image/png,image/jpeg,image/webp">

                    @if($hasUploadedLogo)
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                            <label class="form-check-label" for="remove_logo">Hapus logo unggahan dan kembali ke logo bawaan</label>
                        </div>
                    @endif
                </div>
            </div>

            <div class="adm-card mt-4">
                <div class="adm-card-head"><h2>Video latar layar live</h2></div>
                <div class="adm-card-body">
                    @if($videoUrl)
                        <video src="{{ $videoUrl }}" class="w-100 rounded mb-2" style="max-height:180px;background:#000" muted loop playsinline controls preload="metadata"></video>
                        <div class="small adm-muted mb-3">Video unggahan. Pilih latar <b>Video</b> di Kontrol Live.</div>
                    @endif
                    <input type="file" name="background_video" class="form-control" accept="video/mp4,video/webm">
                    <div class="form-text">MP4/WEBM, maks. 38 MB (batas unggah PHP 40 MB). Pakai video yang tenang (tanpa teks) agar tulisan tetap terbaca. Pilihan latar <b>Video</b> baru muncul di Kontrol Live setelah video diunggah.</div>
                    @if($hasUploadedVideo)
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_video" value="1" id="remove_video">
                            <label class="form-check-label" for="remove_video">Hapus video unggahan</label>
                        </div>
                    @endif
                </div>
            </div>

            <div class="adm-card mt-4">
                <div class="adm-card-head"><h2>Identitas</h2></div>
                <div class="adm-card-body">
                    <label for="church_name" class="form-label fw-semibold">Nama gereja / jemaat</label>
                    <input type="text" name="church_name" id="church_name" class="form-control" maxlength="120" value="{{ old('church_name', $churchName) }}" placeholder="Contoh: GPI Papua Jemaat Imanuel">
                    <div class="form-text">Tampil di menu samping dan judul halaman.</div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4">Simpan Pengaturan</button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="adm-card">
                <div class="adm-card-head"><h2>Pratinjau</h2></div>
                <div class="adm-card-body">
                    <div class="small fw-semibold adm-muted mb-2">Di layar live (latar gelap)</div>
                    <div class="adm-logo-preview mb-3"><img src="{{ $logoUrl }}" alt="Pratinjau logo" class="js-logo-preview" onerror="this.style.opacity=.15"></div>
                    <div class="small fw-semibold adm-muted mb-2">Di latar terang</div>
                    <div class="adm-logo-preview is-light"><img src="{{ $logoUrl }}" alt="" class="js-logo-preview" onerror="this.style.opacity=.15"></div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('logo');
        const zone = document.getElementById('dropzone');
        const name = document.getElementById('fileName');

        function preview(file) {
            if (!file) return;
            if (!['image/png', 'image/jpeg', 'image/webp'].includes(file.type)) {
                name.textContent = 'Format tidak didukung: gunakan PNG, JPG, atau WEBP.';
                name.classList.replace('text-primary', 'text-danger');
                input.value = '';
                return;
            }
            name.textContent = file.name + ' (' + Math.round(file.size / 1024) + ' KB). Tekan Simpan untuk memakai logo ini.';
            name.classList.replace('text-danger', 'text-primary');
            const url = URL.createObjectURL(file);
            document.querySelectorAll('.js-logo-preview').forEach((img) => { img.src = url; img.style.opacity = 1; });
            const remove = document.getElementById('remove_logo');
            if (remove) remove.checked = false;
        }

        input.addEventListener('change', () => preview(input.files[0]));
        ['dragenter', 'dragover'].forEach((t) => zone.addEventListener(t, (e) => { e.preventDefault(); zone.classList.add('is-over'); }));
        ['dragleave', 'drop'].forEach((t) => zone.addEventListener(t, (e) => { e.preventDefault(); zone.classList.remove('is-over'); }));
        zone.addEventListener('drop', (e) => {
            const file = e.dataTransfer.files[0];
            if (!file) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            preview(file);
        });
    })();
</script>
@endpush
