# Panduan Deploy ke Server Online

Panduan ini untuk memindahkan Sistem Multimedia GPI dari komputer lokal (XAMPP) ke server online, supaya bisa dibuka dari mana saja lewat alamat seperti `https://multimedia.namagereja.org`.

Ada dua pilihan server:

- **A. VPS** (mis. DigitalOcean, Biznet Gio, IDCloudHost VPS): paling stabil dan paling bebas diatur. **Disarankan.**
- **B. Hosting cPanel** (shared hosting): lebih murah. Syaratnya: PHP 8.1+ dan bisa mengatur folder publik / *document root*.

---

## 1. Kebutuhan server

| Komponen | Versi / catatan |
|---|---|
| PHP | 8.1 – 8.3 |
| Ekstensi PHP | `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `xml` (`simplexml`), `curl`, `ctype`, `tokenizer`, `bcmath`, `gd` |
| Database | MySQL 5.7+ / MariaDB 10.4+ |
| Web server | Nginx atau Apache (dengan `mod_rewrite`) |
| HTTPS | **Wajib**. Kamera, mode offline (tetap tayang saat internet putus), dan remote HP hanya jalan lewat https. Gunakan Let's Encrypt (gratis) atau SSL dari cPanel. |
| Node.js | Hanya untuk *build* aset. Boleh dijalankan di komputer lokal, lalu folder `public/build` diunggah. |

Batas unggah PHP untuk gambar warta (maks. 20 MB) dan video latar (maks. 38 MB), di `php.ini`:

```ini
upload_max_filesize = 40M
post_max_size = 40M
memory_limit = 256M
```

Nginx: `client_max_body_size 40M;`.

---

## 2. Siapkan file aplikasi

Di komputer lokal (folder proyek):

```bash
npm ci
npm run build          # menghasilkan public/build (CSS, JS, font, tanpa CDN)
```

Lalu unggah seluruh folder proyek ke server lewat `git clone` atau unggah ZIP. **Jangan ikut mengunggah:**

- file `.env` dari komputer lokal (server punya `.env` sendiri);
- folder `node_modules`;
- file `public/hot` (kalau ada, semua tampilan akan rusak di server).

---

## 3. Langkah di server (VPS)

```bash
cd /var/www/multimedia                     # folder aplikasi
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
nano .env                                  # isi APP_URL, DB_*, ADMIN_* (lihat bagian 5)

php artisan migrate --force                # membuat tabel
php artisan db:seed --force                # admin pertama + 5 template + 37 lagu (aman diulang)
php artisan storage:link                   # agar logo & gambar warta bisa tampil

# Hak akses folder tulis
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Percepat aplikasi (ulangi setiap kali .env / kode berubah)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Contoh konfigurasi Nginx

```nginx
server {
    listen 80;
    server_name multimedia.namagereja.org;
    root /var/www/multimedia/public;          # WAJIB folder public, bukan folder proyek
    index index.php;
    client_max_body_size 40M;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location /build/ { expires 1y; add_header Cache-Control "public, immutable"; }
    location ~ /\.(?!well-known) { deny all; }
    location ^~ /storage/ { location ~ \.php$ { deny all; } }   # unggahan tidak boleh dieksekusi
}
```

Lalu aktifkan HTTPS: `sudo certbot --nginx -d multimedia.namagereja.org`.

---

## 4. Langkah di hosting cPanel

1. **Buat database** di *MySQL Databases*: database, user, dan beri user itu semua hak.
2. **Unggah** proyek ke folder di luar `public_html`, mis. `/home/akun/multimedia`.
3. **Arahkan domain/subdomain** ke `/home/akun/multimedia/public` (*Domains → Document Root*).
   - Bila hosting tidak mengizinkan mengubah document root: salin isi folder `public/` ke `public_html/`, lalu di `public_html/index.php` ubah kedua baris `__DIR__.'/../...'` menjadi `__DIR__.'/../multimedia/...'`.
4. **Terminal cPanel** (atau *Setup PHP App*): jalankan perintah di bagian 3, mulai dari `composer install`.
   - Tanpa terminal: jalankan `composer install` dan semua perintah `php artisan ...` di komputer lokal, lalu unggah folder `vendor` juga. Impor database dari phpMyAdmin lokal ke phpMyAdmin hosting.
5. **`storage:link` diblokir?** Buat symlink lewat *File Manager*, atau minta bantuan admin hosting: `public/storage` → `storage/app/public`.
6. Aktifkan **SSL** di *SSL/TLS Status* / *Let's Encrypt*.

File `public/.htaccess` sudah menangani rewrite URL, blokir file tersembunyi, kompresi, dan cache aset. Folder unggahan juga sudah punya `.htaccess` yang melarang menjalankan skrip.

---

## 5. Isi `.env` di server

```ini
APP_ENV=production
APP_DEBUG=false                 # WAJIB false
APP_URL=https://multimedia.namagereja.org
TRUSTED_PROXIES=                # isi * bila memakai Cloudflare / proxy hosting

DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

ADMIN_EMAIL=admin@namagereja.org
ADMIN_PASSWORD=                 # kosong = dibuatkan acak & ditampilkan saat db:seed

SESSION_SECURE_COOKIE=true
LOG_CHANNEL=daily
LOG_LEVEL=warning
```

Semua pilihan lain sudah dijelaskan di `.env.example`.

---

## 6. Setelah online: daftar periksa

- [ ] Buka `https://alamat-anda/up`. Harus muncul `{"status":"ok","database":true}`. Alamat ini bisa dipakai layanan pemantau uptime (mis. UptimeRobot).
- [ ] Masuk dengan akun admin, lalu **ganti kata sandi** di *Profil Saya*.
- [ ] Buat akun untuk tiap operator di menu **Pengguna**. Pendaftaran umum sengaja ditutup.
- [ ] Unggah logo gereja di **Pengaturan & Logo**.
- [ ] Buka satu jadwal → **Kontrol Live** → **Tayangkan**, lalu cek layar live dan kamera.
- [ ] Untuk 2 komputer: salin tautan **Link 2 Komputer**, lalu buka di komputer proyektor (login dengan akun operator).
- [ ] Tarik satu ayat Alkitab sekali sebelum ibadah. Pasal yang sudah pernah ditarik tersimpan di server.
- [ ] Buka Kontrol Live dan Layar Live sekali saat internet normal. Sesudah itu keduanya tetap bisa dibuka dan dipakai walau internet putus.
- [ ] Coba **Remote HP** (tab Tayangan Slide → pindai QR). HP harus bisa membuka alamat web dan login.

---

## 7. Pembaruan aplikasi berikutnya

```bash
php artisan down --retry=60          # tampilkan halaman "Sedang dalam pemeliharaan"
git pull                             # atau unggah file baru
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

Folder `public/build` hasil `npm run build` ikut diunggah bila ada perubahan tampilan.

> **Jangan** jalankan `php artisan cache:clear` atau `optimize:clear` menjelang ibadah. Perintah itu ikut menghapus simpanan pasal Alkitab dan status layar live. Untuk memperbarui konfigurasi cukup `php artisan config:cache`.

---

## 8. Cadangan (backup)

Simpan dua hal berikut, idealnya otomatis setiap minggu:

1. **Database:** `mysqldump -u USER -p NAMA_DB > backup-$(date +%F).sql`
2. **Folder unggahan:** `storage/app/public` (logo & gambar warta).

Contoh cron di VPS (setiap Senin 02.00, simpan 8 minggu terakhir):

```cron
0 2 * * 1 mysqldump -u USER -pSANDI NAMA_DB | gzip > /home/backup/db-$(date +\%F).sql.gz && find /home/backup -name 'db-*.sql.gz' -mtime +56 -delete
```

---

## 9. Lupa kata sandi / terkunci

Di terminal server:

```bash
php artisan admin:buat --email=admin@namagereja.org
```

Perintah ini membuat akun admin baru, atau mengganti kata sandi akun dengan email tersebut.

---

## 10. Keamanan yang sudah terpasang

- Pendaftaran umum ditutup. Akun hanya dibuat oleh pengguna yang sudah masuk.
- Percobaan masuk dibatasi 20 kali per menit. API ayat/lagu dibatasi 60 kali per menit.
- Header keamanan: anti-clickjacking, `nosniff`, kebijakan kamera, dan HSTS di https.
- Halaman yang butuh login tidak disimpan di cache browser/proxy.
- Unggahan diperiksa dari isi file, bukan dari nama file. SVG dan `.php` ditolak. Folder unggahan tidak bisa menjalankan skrip.
- `robots.txt` dan `noindex`: aplikasi tidak muncul di mesin pencari.
- Halaman error ramah pengguna: tidak membocorkan detail teknis selama `APP_DEBUG=false`.
