# Sistem Multimedia GPI Papua

Aplikasi web untuk pelayanan multimedia ibadah: susun tata ibadah, tarik lagu dan ayat Alkitab, lalu tayangkan ke layar jemaat dengan kontrol seperti PowerPoint.

## Fitur

- **Template tata ibadah:** Minggu I, II, III, Kunci Bulan, dan Minggu V. Isinya struktur dan kalimat liturgi lengkap dengan label PF/PNT/JEMAAT.
- **Database lagu:** KJ, NKB, PKJ, NR, dan DSL, dengan nomor bait asli. Tombol **Tarik** mengisi lirik ke jadwal.
- **Alkitab Terjemahan Baru (TB):** cukup ketik referensi bebas (`yoh 3:16-18`, `Mzm 23`, `Yes 45:1-8; Mat 5:1-12`). Pasal yang sudah ditarik disimpan, sehingga tetap bisa dipakai saat offline.
- **Kontrol live mirip PowerPoint:** pratinjau slide saat ini dan berikutnya, thumbnail, pintasan keyboard, timer, layar hitam/putih, dan latar animasi ringan.
- **Layar live:** area teks diukur dalam sentimeter (default TV 55"). Bisa dijalankan di 1 PC (2 monitor) atau 2 komputer lewat jaringan.
- **Kontrol Live seperti PowerPoint:** mode persiapan & mode tayang (F5 / Shift+F5); klik satu kali tidak pernah mengganti layar live. Ada juga cari slide (Ctrl+F), lagu dadakan (L), teks cepat di bawah layar (K), sembunyikan slide (H), catatan operator, uji tayang, dan remote dari HP (QR).
- **Persiapan cepat:** buat jadwal dari teks/PDF buletin (PDF booklet disusun ulang otomatis), duplikat jadwal, dan cek kesiapan ibadah di Dashboard.
- **Cetak buletin:** tata ibadah dicetak sebagai booklet A4 dilipat atau halaman A5.
- **Tampilan:** empat gaya slide lirik (Nomor Besar, Terang, Klasik, Rata Kiri) dengan nomor bait/REFF besar di pojok kiri, lirik nyanyian berwarna emas, sampul berlogo besar, preset warna masa raya gerejawi, latar video, dan peringatan kontras.
- **Tetap tayang saat offline:** Kontrol Live dan Layar Live di satu komputer tetap jalan walau internet putus.
- **Pengelolaan:** logo gereja, warta sinode (slideshow gambar), akun operator, dan cetak lirik.
- **Riwayat:** siapa mengubah jadwal, lagu, template, pengguna, dan pengaturan.

## Menjalankan di komputer lokal (XAMPP)

```bash
composer install
npm install && npm run build
cp .env.example .env          # lalu ubah: APP_ENV=local, APP_DEBUG=true, APP_URL=http://localhost, DB_*
php artisan key:generate
php artisan migrate
php artisan db:seed           # admin pertama + template + lagu (aman diulang)
php artisan storage:link
php artisan serve
```

Lupa kata sandi admin? Jalankan `php artisan admin:buat --email=alamat@email`.

## Deploy ke server online

Lihat **[docs/DEPLOY.md](docs/DEPLOY.md)**: kebutuhan server, langkah VPS dan cPanel, isi `.env`, backup, dan daftar periksa keamanan.

## Tes

```bash
php artisan test
```

Tes memakai SQLite di memori, jadi database MySQL Anda tidak tersentuh.
