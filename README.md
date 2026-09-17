# WABlast — WhatsApp Blast Management System

Panduan instalasi, deployment, konfigurasi aaPanel, Cloudflare Tunnel, Laravel Queue Worker, dan pembaruan aplikasi melalui GitHub.

**WABlast** adalah aplikasi manajemen pengiriman pesan WhatsApp yang dibangun menggunakan Laravel dan terintegrasi dengan Evolution API.

---

## 📋 Daftar Isi

1. [Informasi Proyek](#-informasi-proyek)
2. [Arsitektur Sistem](#-arsitektur-sistem)
3. [Spesifikasi Server](#-spesifikasi-server)
4. [Persyaratan Sistem](#-persyaratan-sistem)
5. [Struktur Deployment](#-struktur-deployment)
6. [Instalasi dan Deployment](#-instalasi-dan-deployment)
7. [Konfigurasi Environment](#-konfigurasi-environment)
8. [Konfigurasi Database](#-konfigurasi-database)
9. [Konfigurasi aaPanel dan Nginx](#-konfigurasi-aapanel-dan-nginx)
10. [Konfigurasi Cloudflare Tunnel](#-konfigurasi-cloudflare-tunnel)
11. [Konfigurasi Laravel Storage](#-konfigurasi-laravel-storage)
12. [Build Frontend](#-build-frontend)
13. [Queue Worker dan Scheduler](#-queue-worker-dan-scheduler)
14. [Pembaruan Aplikasi melalui Git](#-pembaruan-aplikasi-melalui-git)
15. [Troubleshooting](#-troubleshooting)
16. [Checklist Deployment](#-checklist-deployment)
17. [Lisensi](#-lisensi)

---

## 🚀 Informasi Proyek

| Informasi          | Detail                              |
| ------------------ | ----------------------------------- |
| Nama aplikasi      | WABlast                             |
| Fungsi             | Manajemen pengiriman pesan WhatsApp |
| Framework          | Laravel                             |
| Integrasi WhatsApp | Evolution API                       |
| Web server         | Nginx                               |
| Panel server       | aaPanel                             |
| Sistem operasi     | Ubuntu 22.04.5 LTS                  |
| PHP                | 8.4                                 |
| Frontend bundler   | Vite                                |
| Package manager    | Composer dan NPM                    |
| Domain produksi    | `wablast.sirkelta.my.id`            |
| Deployment         | aaPanel + Cloudflare Tunnel         |
| Repository         | GitHub                              |

> **Catatan:** Versi Laravel, database, dan detail modul aplikasi perlu disesuaikan dengan konfigurasi aktual pada source code.

---

## 🏗️ Arsitektur Sistem

```text
                    INTERNET
                        │
                        ▼
                 CLOUDFLARE
                 Zero Trust
                 Tunnel
                        │
                        ▼
              wablast.sirkelta.my.id
                        │
                        ▼
                  aaPanel
                  Nginx
                        │
                        ▼
              Laravel WABlast
                        │
             ┌──────────┼──────────┐
             ▼          ▼          ▼
          PHP-FPM    Database   Queue Worker
             │                     │
             │                     ▼
             │               Evolution API
             │                     │
             │                     ▼
             │                 WhatsApp
             │
             ▼
           Storage
           Logs
```

### Komponen Utama

| Komponen          | Fungsi                                                             |
| ----------------- | ------------------------------------------------------------------ |
| Cloudflare Tunnel | Menghubungkan domain publik ke server lokal atau jaringan internal |
| Nginx             | Menangani permintaan HTTP/HTTPS                                    |
| PHP-FPM           | Menjalankan aplikasi Laravel                                       |
| Laravel           | Menangani logika aplikasi dan autentikasi                          |
| Database          | Menyimpan data aplikasi                                            |
| Queue Worker      | Memproses pekerjaan antrean di latar belakang                      |
| Evolution API     | Gateway integrasi WhatsApp                                         |
| Vite              | Mengompilasi aset CSS dan JavaScript                               |

---

## 🖥️ Spesifikasi Server

### Informasi Server yang Telah Digunakan

Berdasarkan pemeriksaan server pada September 2026:

| Komponen           | Hasil                                                  |
| ------------------ | ------------------------------------------------------ |
| Hostname           | `web-server`                                           |
| OS                 | Ubuntu 22.04.5 LTS                                     |
| Kernel             | `6.17.2-1-pve`                                         |
| Arsitektur         | `x86_64`                                               |
| PHP-FPM            | PHP 8.4                                                |
| Web server         | Nginx melalui aaPanel                                  |
| Direktori aplikasi | `/www/wwwroot/aaPanel/website/wablast.sirkelta.my.id/` |

### Pemeriksaan Sistem

```bash
# Informasi sistem operasi
cat /etc/os-release

# Informasi kernel
uname -a

# Informasi PHP
php -v

# Informasi Composer
composer --version

# Informasi Node.js dan NPM
node -v
npm -v

# Status disk
df -h

# Status RAM
free -h
```

---

## 🛠️ Persyaratan Sistem

Pastikan komponen berikut telah tersedia di server.

### 1. Web Server

- Nginx 1.20 atau lebih baru.
- aaPanel telah terpasang dan dapat diakses.
- Domain telah diarahkan atau dikonfigurasi melalui Cloudflare Tunnel.

### 2. PHP

PHP 8.4 sesuai dengan konfigurasi server saat ini.

Ekstensi yang umumnya diperlukan oleh Laravel:

```text
bcmath
ctype
curl
fileinfo
gd
json
mbstring
openssl
pdo
tokenizer
xml
zip
```

Tambahkan ekstensi database sesuai driver yang digunakan:

```text
pdo_mysql
```

atau:

```text
pdo_pgsql
```

> Pastikan ekstensi yang dipakai sesuai dengan `DB_CONNECTION` pada file `.env`.

### 3. Composer

```bash
composer --version
```

Gunakan Composer 2.x yang kompatibel dengan dependensi proyek.

### 4. Node.js dan NPM

Periksa versi yang terpasang:

```bash
node -v
npm -v
```

Pada server yang digunakan, Node.js yang terdeteksi adalah:

```text
Node.js v20.20.0
NPM v10.8.2
```

> **Peringatan kompatibilitas:** Log `npm install` menunjukkan `concurrently@10.0.5` membutuhkan Node.js `>=22`. Pertimbangkan peningkatan Node.js ke versi 22 LTS atau versi yang sesuai dengan `package.json`.

---

## 📁 Struktur Deployment

Direktori aplikasi yang digunakan:

```text
/www/wwwroot/aaPanel/website/wablast.sirkelta.my.id/
```

Struktur direktori penting Laravel:

```text
wablast.sirkelta.my.id/
├── app/
├── bootstrap/
│   └── cache/
├── config/
├── database/
├── public/
│   ├── index.php
│   ├── build/
│   └── test.php           # File pengujian sementara
├── resources/
├── routes/
├── storage/
│   ├── app/
│   ├── framework/
│   │   ├── cache/
│   │   │   └── data/
│   │   ├── sessions/
│   │   ├── testing/
│   │   └── views/
│   └── logs/
│       └── laravel.log
├── .env
├── artisan
├── composer.json
├── package.json
└── vite.config.js
```

> `public/test.php` dibuat untuk pengujian PHP-FPM. Hapus file tersebut setelah pengujian selesai.

---

# 📦 Instalasi dan Deployment

## Langkah 1 — Masuk ke Direktori Aplikasi

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id
```

Pastikan direktori aplikasi benar:

```bash
pwd
ls -la
```

---

## Langkah 2 — Instalasi Dependensi PHP

Untuk instalasi produksi:

```bash
composer install --no-dev --optimize-autoloader
```

Jika proyek baru dan belum memiliki file `.env`:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

> Jangan menjalankan `migrate:fresh` pada database produksi tanpa memastikan bahwa seluruh data telah dicadangkan. Perintah tersebut dapat menghapus seluruh tabel.

---

## Langkah 3 — Instalasi Dependensi Frontend

Jalankan dari direktori utama aplikasi:

```bash
npm install
```

Kemudian build aset produksi:

```bash
npm run build
```

Output build yang berhasil diperoleh pada server:

```text
public/build/manifest.json
public/build/assets/app-B58PoiOh.css
public/build/assets/app-D8VlipJK.js
```

Contoh hasil build:

```text
vite v8.2.2 building client environment for production...
✓ 4 modules transformed.
✓ built in 1.10s
```

---

# ⚙️ Konfigurasi Environment

Edit file `.env`:

```bash
nano /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id/.env
```

Contoh konfigurasi dasar:

```env
APP_NAME="WABlast"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://wablast.sirkelta.my.id

LOG_CHANNEL=stack
LOG_LEVEL=error

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=wablast_db
DB_USERNAME=wablast_user
DB_PASSWORD=password_db_anda

# Cache dan Queue
CACHE_STORE=file
QUEUE_CONNECTION=database

# Session
SESSION_DRIVER=file

# Evolution API
EVOLUTION_API_URL=https://evolution.domainanda.com
EVOLUTION_API_APIKEY=GlobalAPIKeyAnda
WA_INSTANCE_NAME=wablast
```

> **Penting:** Contoh di atas menggunakan PostgreSQL sebagai contoh karena server menampilkan modul `pdo_pgsql`. Pastikan `DB_CONNECTION`, port, dan kredensial benar-benar sesuai dengan database aplikasi. Jangan mengganti driver database hanya berdasarkan log ekstensi.

Setelah mengubah `.env`, jalankan:

```bash
php artisan config:clear
```

---

# 🗄️ Konfigurasi Database

## 1. Pemeriksaan Koneksi Database

Pastikan database dan pengguna database telah dibuat di server.

Periksa konfigurasi aplikasi:

```bash
php artisan about
```

Jika database telah dikonfigurasi, jalankan migrasi:

```bash
php artisan migrate --force
```

Jika aplikasi memiliki seeder yang memang dibutuhkan:

```bash
php artisan db:seed --force
```

### Instalasi Baru

Untuk instalasi baru dan database yang boleh dihapus:

```bash
php artisan migrate:fresh --seed --force
```

> Jangan menggunakan `migrate:fresh` untuk pembaruan rutin pada server produksi.

---

## 2. Akun Default

Jika aplikasi memiliki seeder akun bawaan, dokumentasikan akun setelah memastikan data aktual dari source code.

| Role       | Username      | Email         | Password      |
| ---------- | ------------- | ------------- | ------------- |
| Superadmin | Sesuai seeder | Sesuai seeder | Sesuai seeder |
| Admin      | Sesuai seeder | Sesuai seeder | Sesuai seeder |
| Pengguna   | Sesuai seeder | Sesuai seeder | Sesuai seeder |

> Ganti informasi di atas dengan kredensial yang benar-benar tersedia pada seeder aplikasi. Segera ubah password default setelah instalasi.

---

# 🌐 Konfigurasi aaPanel dan Nginx

## 1. Tambahkan Website di aaPanel

Buka:

**aaPanel → Website → Add Site**

Gunakan konfigurasi berikut:

| Pengaturan        | Nilai                                                 |
| ----------------- | ----------------------------------------------------- |
| Domain            | `wablast.sirkelta.my.id`                              |
| Site Directory    | `/www/wwwroot/aaPanel/website/wablast.sirkelta.my.id` |
| Running Directory | `/public`                                             |
| PHP Version       | PHP 8.4                                               |

**Running Directory `/public` wajib digunakan** agar file internal Laravel tidak dapat diakses langsung melalui web.

---

## 2. Konfigurasi Nginx

Contoh konfigurasi:

```nginx
server {
    listen 80;
    server_name wablast.sirkelta.my.id;

    root /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id/public;

    index index.php index.html;

    charset utf-8;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico {
        access_log off;
        log_not_found off;
    }

    location = /robots.txt {
        access_log off;
        log_not_found off;
    }

    location ~ \.php$ {
        include fastcgi_params;

        fastcgi_pass unix:/tmp/php-cgi-84.sock;

        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

> Socket PHP-FPM dapat berbeda pada setiap instalasi aaPanel. Periksa socket PHP 8.4 yang sebenarnya sebelum menerapkan konfigurasi.

### Periksa Konfigurasi Nginx

```bash
nginx -t
```

Jika konfigurasi benar, muat ulang Nginx:

```bash
systemctl reload nginx
```

---

# ☁️ Konfigurasi Cloudflare Tunnel

Cloudflare Tunnel digunakan untuk menghubungkan domain publik ke server tanpa harus membuka port publik secara langsung.

## 1. Membuat Tunnel

1. Buka dashboard **Cloudflare Zero Trust**.
2. Masuk ke **Networks → Tunnels**.
3. Buat tunnel baru.
4. Tentukan nama tunnel, misalnya `wablast-local-tunnel`.
5. Pasang konektor `cloudflared` pada server yang menjalankan aaPanel.

### Instalasi Cloudflared — Contoh Linux AMD64

```bash
curl -L --output cloudflared.deb \
https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb

sudo dpkg -i cloudflared.deb
```

Instalasi sebagai layanan menggunakan token:

```bash
sudo cloudflared service install TOKEN_CLOUDFLARE_ANDA
```

> Gunakan token tunnel milik Anda sendiri. Jangan menyimpan token di repository GitHub.

---

## 2. Public Hostname

Konfigurasi Public Hostname:

| Pengaturan   | Nilai                 |
| ------------ | --------------------- |
| Subdomain    | `wablast`             |
| Domain       | `sirkelta.my.id`      |
| Service Type | HTTP                  |
| Service URL  | `http://localhost:80` |

Jika Nginx berjalan pada alamat IP lokal tertentu, gunakan alamat tersebut sesuai jaringan server.

---

## 3. Konfigurasi SSL/TLS

Untuk koneksi Cloudflare ke origin melalui HTTP, konfigurasi SSL Cloudflare perlu disesuaikan dengan arsitektur jaringan.

Rekomendasi:

- Gunakan **Full (strict)** jika origin telah memiliki sertifikat yang valid.
- Pastikan URL aplikasi menggunakan `https://`.
- Pastikan Laravel mengenali skema HTTPS ketika berada di belakang Cloudflare.
- Periksa konfigurasi trusted proxies sesuai versi Laravel yang digunakan.

> Hindari menggunakan Flexible SSL jika memungkinkan. Pastikan pengaturan SSL dan proxy Laravel konsisten untuk mencegah redirect berulang dan masalah Mixed Content.

---

# 📂 Konfigurasi Laravel Storage

Pada server, direktori Laravel berikut telah dibuat:

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id

mkdir -p storage/framework/{sessions,views,cache,testing}
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache
```

## 1. Atur Kepemilikan Direktori

```bash
chown -R www:www storage bootstrap/cache
```

## 2. Atur Hak Akses

Gunakan hak akses yang lebih terbatas daripada `777` jika memungkinkan:

```bash
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

Jika lingkungan server memerlukan izin grup yang berbeda, sesuaikan pengguna dan grup berdasarkan konfigurasi PHP-FPM.

> **Catatan keamanan:** `chmod -R 777` memang dapat mengatasi sebagian masalah izin, tetapi tidak disarankan pada server produksi karena memberikan izin baca, tulis, dan eksekusi kepada semua pengguna. Gunakan izin minimum yang diperlukan.

## 3. Buat File Log Laravel

```bash
touch storage/logs/laravel.log

chown www:www storage/logs/laravel.log
chmod 664 storage/logs/laravel.log
```

## 4. Storage Link

Jalankan jika aplikasi menggunakan penyimpanan publik Laravel:

```bash
php artisan storage:link
```

---

# 🧹 Pembersihan Cache Laravel

Pada proses perbaikan server, perintah berikut telah berhasil dijalankan:

```bash
rm -f bootstrap/cache/*.php

php artisan optimize:clear

composer dump-autoload
```

Perintah tersebut berfungsi untuk:

| Perintah                      | Fungsi                                                                 |
| ----------------------------- | ---------------------------------------------------------------------- |
| `rm -f bootstrap/cache/*.php` | Menghapus file cache bootstrap PHP                                     |
| `php artisan optimize:clear`  | Membersihkan cache konfigurasi, route, view, dan cache Laravel lainnya |
| `composer dump-autoload`      | Membuat ulang autoloader Composer                                      |

Untuk optimasi produksi setelah konfigurasi selesai:

```bash
php artisan optimize
```

> Jalankan `config:cache` hanya setelah memastikan konfigurasi `.env` sudah benar.

---

# 🎨 Build Frontend

## Perintah Build

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id

npm install

npm run build
```

## Status Build Server

| Tahap                             | Status                                         |
| --------------------------------- | ---------------------------------------------- |
| `npm run build` sebelum instalasi | Gagal — `vite: command not found`              |
| `npm install`                     | Berhasil                                       |
| `npm run build` setelah instalasi | Berhasil                                       |
| Output `public/build`             | Berhasil dibuat                                |
| Audit dependensi NPM              | 0 vulnerabilities                              |
| Peringatan Node.js                | `concurrently@10.0.5` membutuhkan Node.js >=22 |

### Jika Vite Tidak Ditemukan

Jalankan:

```bash
npm install
npm run build
```

Jika masih gagal, periksa:

```bash
ls -la node_modules/.bin/vite
cat package.json
```

Pastikan Vite terdaftar dalam dependensi proyek dan instalasi NPM selesai tanpa error.

---

# ⏱️ Queue Worker dan Scheduler

Jika WABlast menggunakan Laravel Queue untuk mengirim pesan secara asinkron, Queue Worker harus berjalan secara terus-menerus.

## 1. Laravel Scheduler

Buka:

**aaPanel → Cron**

Tambahkan tugas terjadwal setiap menit.

**Nama:** `WABlast Laravel Schedule`

**Jadwal:**

```text
* * * * *
```

**Script:**

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id && \
/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Sesuaikan lokasi PHP jika berbeda:

```bash
which php
```

---

## 2. Queue Worker melalui Supervisor

Buka:

**aaPanel → App Store → Supervisor Manager**

Contoh konfigurasi:

| Pengaturan    | Nilai                                                 |
| ------------- | ----------------------------------------------------- |
| Name          | `wablast-worker`                                      |
| Run User      | `www`                                                 |
| Run Directory | `/www/wwwroot/aaPanel/website/wablast.sirkelta.my.id` |

**Start Command:**

```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

> Pastikan `QUEUE_CONNECTION` sesuai dengan mekanisme queue yang digunakan. Jika memakai database queue, tabel jobs harus tersedia. Jika memakai Redis, pastikan Redis telah terpasang dan dikonfigurasi.

### Perintah Pengujian Queue

```bash
php artisan queue:work --once
```

Untuk memeriksa pekerjaan yang gagal:

```bash
php artisan queue:failed
```

Untuk mencoba ulang pekerjaan yang gagal:

```bash
php artisan queue:retry all
```

---

# 🔄 Pembaruan Aplikasi melalui Git

## 1. Persiapan

Masuk ke direktori aplikasi:

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id
```

Periksa status Git:

```bash
git status
```

Periksa branch yang aktif:

```bash
git branch --show-current
```

---

## 2. Backup Sebelum Update

Sebelum melakukan pembaruan, lakukan backup database dan file penting.

Contoh backup `.env`:

```bash
cp .env .env.backup
```

> Backup database harus menggunakan perintah yang sesuai dengan DBMS yang dipakai, misalnya `pg_dump` untuk PostgreSQL atau `mysqldump` untuk MySQL.

---

## 3. Perintah Update Backend

Contoh alur pembaruan aplikasi:

```bash
git pull origin main

composer install --no-dev --optimize-autoloader

php artisan migrate --force

php artisan optimize:clear

php artisan optimize
```

Jika source code memiliki perubahan frontend:

```bash
npm install
npm run build
```

### Perintah Update Lengkap

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id && \
git pull origin main && \
composer install --no-dev --optimize-autoloader && \
php artisan migrate --force && \
php artisan optimize:clear && \
npm install && \
npm run build && \
php artisan optimize
```

> Gunakan perintah update sesuai kebutuhan. Jika `npm install` tidak diperlukan, Anda dapat melewatinya. Jangan menjalankan pembaruan secara otomatis apabila `git pull`, migrasi, atau build mengalami kegagalan.

---

# 🧪 Pemeriksaan dan Troubleshooting

## 1. Memeriksa Log Nginx Website

```bash
tail -n 25 /www/wwwlogs/wablast.sirkelta.my.id.error.log
```

Melihat log secara langsung:

```bash
tail -f /www/wwwlogs/wablast.sirkelta.my.id.error.log
```

---

## 2. Memeriksa Log PHP-FPM

```bash
tail -n 25 /www/server/php/84/var/log/php-fpm.log
```

### Hasil Pemeriksaan

Log PHP-FPM menunjukkan beberapa kali proses PHP-FPM berhasil berjalan dan siap menerima koneksi.

Terdapat peringatan berikut:

```text
[pool www] seems busy
```

Artinya, seluruh proses worker PHP-FPM sedang sibuk menangani permintaan. Jika kondisi ini sering terjadi, periksa penggunaan CPU, RAM, waktu eksekusi aplikasi, dan konfigurasi pool PHP-FPM sebelum menambah jumlah worker.

---

## 3. Peringatan `pdo_pgsql` Dimuat Dua Kali

Pesan yang muncul:

```text
PHP Warning: Module "pdo_pgsql" is already loaded
```

### Pemeriksaan

Cari konfigurasi yang memuat ekstensi tersebut:

```bash
php --ini
```

```bash
grep -Rni "pdo_pgsql" \
/www/server/php/84/etc/php.ini \
/www/server/php/84/etc/php.d/ \
2>/dev/null
```

Jika `pdo_pgsql` dipanggil lebih dari satu kali, hapus salah satu deklarasi yang duplikat.

> Lakukan perubahan melalui konfigurasi PHP aaPanel apabila memungkinkan. Jangan menghapus ekstensi yang masih diperlukan oleh aplikasi.

---

## 4. Peringatan PHP JIT

Pesan yang muncul:

```text
JIT is incompatible with third party extensions
that override zend_execute_ex(). JIT disabled.
```

Artinya, PHP menonaktifkan JIT karena terdapat ekstensi yang tidak kompatibel dengan mekanisme tersebut.

Pemeriksaan:

```bash
php -i | grep -i jit
```

Peringatan ini tidak selalu menyebabkan aplikasi Laravel gagal berjalan. Uji aplikasi terlebih dahulu sebelum melakukan perubahan konfigurasi JIT.

---

## 5. Memeriksa Versi PHP dan Ekstensi

```bash
php -v
```

```bash
php -m
```

Memeriksa driver database:

```bash
php -m | grep -E "pdo|pgsql|mysql"
```

---

## 6. Memeriksa Status Laravel

```bash
php artisan about
```

```bash
php artisan route:list
```

```bash
php artisan config:show database
```

> Hindari menampilkan kredensial database atau informasi rahasia ketika membagikan hasil perintah ke publik.

---

## 7. Menguji PHP-FPM

Buat file pengujian sementara:

```bash
echo "<?php echo 'PHP BERHASIL JALAN'; ?>" \
> public/test.php

chown www:www public/test.php
```

Buka alamat:

```text
https://wablast.sirkelta.my.id/test.php
```

Jika muncul:

```text
PHP BERHASIL JALAN
```

berarti permintaan PHP melalui web server telah berhasil diproses.

**Hapus file setelah pengujian:**

```bash
rm -f public/test.php
```

> Jangan meninggalkan file pengujian PHP di direktori publik server produksi.

---

## 8. Membersihkan Cache Saat Terjadi Error

```bash
php artisan optimize:clear
```

Jika diperlukan:

```bash
rm -f bootstrap/cache/*.php
```

Kemudian:

```bash
composer dump-autoload
```

---

# ✅ Checklist Deployment

## Server dan Web Server

- [ ] Ubuntu dan aaPanel telah berjalan dengan baik.
- [ ] Nginx telah dikonfigurasi.
- [ ] PHP 8.4 telah terpasang.
- [ ] Socket PHP-FPM telah diverifikasi.
- [ ] Root website diarahkan ke folder `/public`.
- [ ] Domain telah dikonfigurasi.

## Laravel

- [ ] Dependensi Composer telah terpasang.
- [ ] File `.env` telah dikonfigurasi.
- [ ] `APP_KEY` telah tersedia.
- [ ] Koneksi database telah diuji.
- [ ] Migrasi telah dijalankan.
- [ ] `storage:link` telah dibuat jika diperlukan.
- [ ] Hak akses `storage` dan `bootstrap/cache` telah sesuai.
- [ ] Cache Laravel telah dibersihkan atau dibangun ulang.

## Frontend

- [ ] Dependensi NPM telah terpasang.
- [ ] Versi Node.js kompatibel.
- [ ] `npm run build` berhasil.
- [ ] Folder `public/build` tersedia.

## WhatsApp dan Background Worker

- [ ] Evolution API dapat diakses.
- [ ] API key telah dikonfigurasi dengan aman.
- [ ] Instance WhatsApp telah terhubung.
- [ ] Queue Worker telah berjalan.
- [ ] Scheduler telah dikonfigurasi.
- [ ] Pengiriman pesan telah diuji.

## Keamanan

- [ ] `APP_DEBUG=false` pada produksi.
- [ ] File `.env` tidak dapat diakses dari publik.
- [ ] File `test.php` telah dihapus.
- [ ] Token Cloudflare tidak disimpan di GitHub.
- [ ] API key tidak disimpan di source code publik.
- [ ] Backup database telah dibuat.
- [ ] Password default telah diubah.
- [ ] Hak akses direktori telah dibatasi.

---

# 🔄 Pembaruan Aplikasi melalui GitHub

Bagian ini menjelaskan cara memperbarui aplikasi WABlast di server aaPanel setelah terdapat perubahan kode pada repository GitHub.

## 📁 Direktori Aplikasi

Seluruh perintah dijalankan dari direktori:

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id
```

---

## 🚀 1. Update Lengkap Aplikasi

Gunakan perintah berikut jika terdapat perubahan kode dari GitHub, termasuk perubahan backend, database, CSS, atau JavaScript.

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id && \
git pull origin main && \
composer install --no-dev --optimize-autoloader && \
php artisan migrate --force && \
php artisan optimize:clear && \
npm install && \
npm run build && \
php artisan optimize && \
php artisan queue:restart
```

### Penjelasan Perintah

| Perintah                                          | Fungsi                                                                         |
| ------------------------------------------------- | ------------------------------------------------------------------------------ |
| `git pull origin main`                            | Mengambil perubahan terbaru dari GitHub                                        |
| `composer install --no-dev --optimize-autoloader` | Memperbarui dependensi PHP                                                     |
| `php artisan migrate --force`                     | Menjalankan migrasi database baru                                              |
| `php artisan optimize:clear`                      | Membersihkan cache Laravel                                                     |
| `npm install`                                     | Memastikan dependensi frontend tersedia                                        |
| `npm run build`                                   | Mengompilasi CSS dan JavaScript terbaru                                        |
| `php artisan optimize`                            | Membangun cache optimasi Laravel                                               |
| `php artisan queue:restart`                       | Meminta Queue Worker memuat kode terbaru setelah menyelesaikan pekerjaan aktif |

> **Catatan:** Perintah di atas menggunakan `&&`. Jika salah satu perintah gagal, perintah berikutnya tidak akan dijalankan.

---

## ⚡ 2. Update Backend Saja

Gunakan perintah ini jika hanya terdapat perubahan pada:

- Controller.
- Model.
- Service.
- Route.
- Middleware.
- File Blade.
- Logika aplikasi Laravel.

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id && \
git pull origin main && \
composer install --no-dev --optimize-autoloader && \
php artisan migrate --force && \
php artisan optimize:clear && \
php artisan optimize && \
php artisan queue:restart
```

> Jika terdapat perubahan file frontend yang dikompilasi melalui Vite, tetap jalankan `npm run build`.

---

## 🎨 3. Update Frontend Saja

Gunakan perintah ini jika terdapat perubahan pada CSS, JavaScript, atau komponen frontend yang membutuhkan proses build Vite.

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id && \
git pull origin main && \
npm install && \
npm run build
```

Jika perubahan frontend menggunakan dependensi baru, `npm install` akan memasangnya sesuai `package.json` dan `package-lock.json`.

---

## 🗄️ 4. Update Database Saja

Jika terdapat migration baru pada repository:

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id && \
git pull origin main && \
php artisan migrate --force && \
php artisan optimize:clear && \
php artisan optimize
```

> Jangan menggunakan `php artisan migrate:fresh` pada server produksi karena perintah tersebut dapat menghapus seluruh tabel dan data.

---

## 🔧 5. Restart Queue Worker

Setelah memperbarui kode yang digunakan oleh Queue Worker, jalankan:

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id && \
php artisan queue:restart
```

Perintah ini memberi sinyal kepada worker Laravel agar berhenti setelah menyelesaikan pekerjaan yang sedang diproses dan memuat kode terbaru saat worker dijalankan kembali oleh Supervisor.

Pastikan Supervisor tetap dalam keadaan aktif.

---

## 🔍 6. Pemeriksaan Setelah Update

Periksa status Git:

```bash
git status
```

Periksa informasi aplikasi Laravel:

```bash
php artisan about
```

Periksa daftar route:

```bash
php artisan route:list
```

Periksa log Laravel:

```bash
tail -n 50 storage/logs/laravel.log
```

Periksa log Nginx:

```bash
tail -n 50 /www/wwwlogs/wablast.sirkelta.my.id.error.log
```

Periksa status proses PHP-FPM:

```bash
systemctl status php-fpm
```

> Nama layanan PHP-FPM dapat berbeda pada instalasi aaPanel. Gunakan pengelola layanan aaPanel atau cari nama layanan PHP-FPM yang sesuai.

---

## ⚠️ 7. Hal yang Perlu Diperhatikan

### Backup Sebelum Update

Sebelum melakukan migrasi atau perubahan besar, buat backup database dan file `.env`.

Contoh backup `.env`:

```bash
cp .env .env.backup
```

Untuk backup database, gunakan utilitas yang sesuai dengan database yang digunakan.

### Jangan Menjalankan Update Jika:

- Ada perubahan lokal yang belum di-commit dan dapat tertimpa oleh `git pull`.
- Migrasi database belum diuji.
- Build frontend mengalami kegagalan.
- Dependensi memiliki konflik versi.
- Backup database belum tersedia untuk perubahan besar.

### Jika `git pull` Mengalami Konflik

Jangan langsung menjalankan `git reset --hard` karena dapat menghapus perubahan lokal.

Periksa terlebih dahulu:

```bash
git status
```

---

## 📌 8. Rekomendasi Perintah Harian

Untuk penggunaan sehari-hari ketika terdapat perubahan kode di GitHub, gunakan perintah berikut:

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id && \
git pull origin main && \
composer install --no-dev --optimize-autoloader && \
php artisan migrate --force && \
php artisan optimize:clear && \
npm install && \
npm run build && \
php artisan optimize && \
php artisan queue:restart
```

**Pastikan proses selesai tanpa error sebelum mengakses kembali aplikasi WABlast.**

# 📝 Catatan Pengembangan

Dokumen ini mencatat konfigurasi deployment WABlast pada server aaPanel dengan domain:

```text
wablast.sirkelta.my.id
```

Perintah dan konfigurasi pada README ini harus disesuaikan dengan versi Laravel, PHP, database, struktur project, dan lingkungan server yang sebenarnya.

---

# 🛡️ Lisensi

Dilindungi Hak Cipta © 2026 WABlast Management System.

Seluruh hak cipta dan penggunaan aplikasi mengikuti ketentuan pemilik proyek.
