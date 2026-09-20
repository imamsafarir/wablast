# WABlast — WhatsApp Blast Management System

Panduan instalasi, deployment, konfigurasi aaPanel, Cloudflare Tunnel, Laravel Queue Worker & Scheduler, dan pembaruan aplikasi melalui GitHub.

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
13. [Konfigurasi Cron aaPanel (Queue & Scheduler)](#-konfigurasi-cron-aapanel-queue--scheduler)
14. [Pembaruan Aplikasi melalui GitHub](#-pembaruan-aplikasi-melalui-github)
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
              ┌─────────┼─────────┐
              ▼         ▼         ▼
           PHP-FPM   Database  Queue Worker
              │                   │
              │                   ▼
              │              Evolution API
              │                   │
              │                   ▼
              │                WhatsApp
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
| PHP-FPM           | Menjalankan aplikasi Laravel                                      |
| Laravel           | Menangani logika aplikasi dan autentikasi                          |
| Database          | Menyimpan data aplikasi                                            |
| Queue Worker      | Memproses antrean pesan di latar belakang                          |
| Evolution API     | Gateway integrasi WhatsApp                                         |
| Vite              | Mengompilasi aset CSS dan JavaScript                               |

---

## 🖥️ Spesifikasi Server

| Komponen           | Hasil                                                 |
| ------------------ | ----------------------------------------------------- |
| Hostname           | `web-server`                                          |
| OS                 | Ubuntu 22.04.5 LTS                                    |
| Kernel             | `6.17.2-1-pve`                                        |
| Arsitektur         | `x86_64`                                              |
| PHP-FPM            | PHP 8.4                                               |
| Web server         | Nginx (aaPanel)                                       |
| Direktori aplikasi | `/www/wwwroot/aaPanel/website/wablast.sirkelta.my.id/` |

### Pemeriksaan Sistem

```bash
# Informasi sistem operasi & kernel
cat /etc/os-release
uname -a

# Informasi runtime & package manager
php -v
composer --version
node -v
npm -v

# Status resource
df -h
free -h
```

---

## 🛠️ Persyaratan Sistem

1. **Web Server:** Nginx 1.20+ via aaPanel, domain terhubung Cloudflare Tunnel.
2. **PHP 8.4:** Ekstensi wajib:
   ```text
   bcmath, ctype, curl, fileinfo, gd, json, mbstring, openssl, pdo, tokenizer, xml, zip
   ```
   Serta driver database yang sesuai (`pdo_mysql` atau `pdo_pgsql`).
3. **Composer:** Versi 2.x.
4. **Node.js & NPM:** Direkomendasikan Node.js LTS (v20 atau v22) & NPM v10+.

---

## 📁 Struktur Deployment

```text
/www/wwwroot/aaPanel/website/wablast.sirkelta.my.id/
├── app/
├── bootstrap/
│   └── cache/
├── config/
├── database/
├── public/
│   ├── index.php
│   └── build/
├── resources/
├── routes/
├── storage/
│   ├── app/
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   └── logs/
│       └── laravel.log
├── .env
├── artisan
├── composer.json
├── package.json
└── vite.config.js
```

---

## 📦 Instalasi dan Deployment

### Langkah 1 — Masuk ke Direktori Aplikasi

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id
```

### Langkah 2 — Instalasi Dependensi PHP

```bash
composer install --no-dev --optimize-autoloader
```

Jika baru setup pertama kali:

```bash
cp .env.example .env
php artisan key:generate
```

### Langkah 3 — Instalasi Dependensi Frontend & Build

```bash
npm install
npm run build
```

---

## ⚙️ Konfigurasi Environment

Edit file konfigurasi `.env`:

```bash
nano /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id/.env
```

Contoh parameter inti:

```env
APP_NAME="WABlast"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://wablast.sirkelta.my.id

LOG_CHANNEL=stack
LOG_LEVEL=error

# Database (sesuaikan driver yang aktif di server)
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

Setelah mengubah `.env`, bersihkan cache konfigurasi:

```bash
php artisan config:clear
```

---

## 🗄️ Konfigurasi Database

Jalankan migrasi database di lingkungan produksi:

```bash
php artisan migrate --force
```

Jika memerlukan data bawaan (seeder):

```bash
php artisan db:seed --force
```

> **Peringatan:** Jangan jalankan `php artisan migrate:fresh` di server produksi karena akan menghapus seluruh data tabel.

---

## 🌐 Konfigurasi aaPanel dan Nginx

### 1. Pengaturan Website di aaPanel

Masuk ke **aaPanel → Website → Add Site**:

- **Domain:** `wablast.sirkelta.my.id`
- **Site Directory:** `/www/wwwroot/aaPanel/website/wablast.sirkelta.my.id`
- **Running Directory:** `/public` *(Wajib diarahkan ke `/public` demi keamanan)*
- **PHP Version:** `PHP-84`

### 2. Nginx Server Block

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

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

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

Uji dan terapkan konfigurasi Nginx:

```bash
nginx -t && systemctl reload nginx
```

---

## ☁️ Konfigurasi Cloudflare Tunnel

1. Masuk ke **Cloudflare Zero Trust** → **Networks** → **Tunnels**.
2. Buat tunnel (misal: `wablast-local-tunnel`) dan jalankan agent di server:
   ```bash
   sudo cloudflared service install TOKEN_CLOUDFLARE_ANDA
   ```
3. Atur **Public Hostname**:
   - Subdomain: `wablast`
   - Domain: `sirkelta.my.id`
   - Service Type: `HTTP`
   - Service URL: `localhost:80` (atau IP lokal server)
4. Set SSL/TLS mode ke **Full (strict)** di dashboard Cloudflare.

---

## 📂 Konfigurasi Laravel Storage

Pastikan folder permission dan log Laravel sudah siap:

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id

# Buat direktori cache dan logs jika belum ada
mkdir -p storage/framework/{sessions,views,cache,testing}
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Atur kepemilikan user www
chown -R www:www storage bootstrap/cache

# Hak akses yang aman (775 folder, 664 file)
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;

# Inisialisasi file log
touch storage/logs/laravel.log
chown www:www storage/logs/laravel.log
chmod 664 storage/logs/laravel.log

# Link storage publik
php artisan storage:link
```

---

## 🎨 Build Frontend

Kompilasi aset CSS dan JavaScript produksi:

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id
npm install
npm run build
```

---

## ⏱️ Konfigurasi Cron aaPanel (Queue & Scheduler)

Pada aaPanel, masuk ke menu **Cron** (Tampilan **Add Task**). Anda perlu menambahkan dua task: **Laravel Scheduler** dan **Queue Worker (Keep-Alive)**.

---

### Task 1: Laravel Queue Worker (Jalan Terus / Auto-Restart)

Agar queue berjalan terus tanpa berhenti, gunakan script *keep-alive* yang berjalan setiap menit untuk memastikan proses `queue:work` selalu aktif di background. Jika proses mati, script akan otomatis menyalakannya kembali.

Isi form di **aaPanel → Cron → Add Task**:

| Field Form aaPanel | Nilai Konfigurasi |
| :--- | :--- |
| **Task type** | `Shell Script` |
| **Task name** | `WABlast Laravel Queue Worker` |
| **Execute cycle** | Pilih `N Minutes` ➔ Isi `1` Minutes (eksekusi setiap 1 menit) |
| **Execute user** | `www` (atau `root`) |
| **Script content** | *(Salin script di bawah)* |

**Script content:**

```bash
if ! pgrep -f "wablast.sirkelta.my.id.*artisan queue:work" > /dev/null; then
    cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id
    nohup /usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600 >> storage/logs/queue.log 2>&1 &
fi
```

> **Penjelasan Script:**
> - `pgrep -f`: Memeriksa apakah worker untuk project ini sedang aktif.
> - Jika tidak aktif, perintah `nohup ... &` akan menyalakan worker di background dan mengarahkan log ke `storage/logs/queue.log`.
> - Parameter `--max-time=3600` me-restart worker setiap 1 jam untuk mencegah kebocoran memori (memory leak), dan cron akan otomatis membangunkannya kembali pada menit berikutnya.

---

### Task 2: Laravel Scheduler (Tugas Terjadwal)

Untuk menjalankan jadwal rutin Laravel (crontab internal):

| Field Form aaPanel | Nilai Konfigurasi |
| :--- | :--- |
| **Task type** | `Shell Script` |
| **Task name** | `WABlast Laravel Scheduler` |
| **Execute cycle** | Pilih `N Minutes` ➔ Isi `1` Minutes |
| **Execute user** | `www` (atau `root`) |
| **Script content** | *(Salin script di bawah)* |

**Script content:**

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

---

### Perintah Pengujian Manual Queue

Untuk menguji queue via terminal:

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id

# Jalankan 1 task antrean saja
php artisan queue:work --once

# Cek daftar job yang gagal
php artisan queue:failed

# Retry semua job yang gagal
php artisan queue:retry all
```

---

## 🔄 Pembaruan Aplikasi melalui GitHub

Gunakan panduan ini setiap kali Anda melakukan update kode dari repository GitHub ke server.

### 📁 Direktori Kerja
Semua perintah dijalankan di dalam root project:
```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id
```

---

### 1. Update Lengkap (Rekomendasi Utama)
Gunakan opsi ini jika terdapat pembaruan menyeluruh (kode backend, database migration, dan aset frontend):

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

---

### 2. Update Backend Saja
Gunakan jika hanya mengedit Controller, Model, Blade, Route, atau dependensi PHP:

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id && \
git pull origin main && \
composer install --no-dev --optimize-autoloader && \
php artisan migrate --force && \
php artisan optimize:clear && \
php artisan optimize && \
php artisan queue:restart
```

---

### 3. Update Frontend Saja
Gunakan jika hanya ada perubahan pada file CSS/JavaScript/Vite:

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id && \
git pull origin main && \
npm install && \
npm run build
```

---

### 4. Update Database Saja
Gunakan jika hanya ada penambahan file Migration:

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id && \
git pull origin main && \
php artisan migrate --force && \
php artisan optimize:clear && \
php artisan optimize
```

---

### 5. Restart Queue Worker
Setelah kode logic WhatsApp/Queue diperbarui, berikan sinyal restart agar worker memuat kode baru:

```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id && \
php artisan queue:restart
```

---

## 🧪 Troubleshooting

### 1. Cek Log Error Nginx
```bash
tail -n 25 /www/wwwlogs/wablast.sirkelta.my.id.error.log
```

### 2. Cek Log Laravel & Queue
```bash
# Log aplikasi
tail -n 50 /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id/storage/logs/laravel.log

# Log background queue worker
tail -n 50 /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id/storage/logs/queue.log
```

### 3. Cek Status Proses Queue Worker
Untuk memastikan worker sedang berjalan di background:
```bash
ps aux | grep "queue:work"
```

### 4. Peringatan Ekstensi Duplikat (`pdo_pgsql already loaded`)
Periksa file `php.ini` atau file konfigurasi scan aaPanel:
```bash
grep -Rni "pdo_pgsql" /www/server/php/84/etc/php.ini /www/server/php/84/etc/php.d/ 2>/dev/null
```
Jika modul dipanggil dua kali, beri tanda komentar `;` pada salah satu baris `extension=pdo_pgsql`.

### 5. Membersihkan Cache Saat Terjadi Anomali
```bash
cd /www/wwwroot/aaPanel/website/wablast.sirkelta.my.id
rm -f bootstrap/cache/*.php
php artisan optimize:clear
composer dump-autoload
php artisan optimize
```

---

## ✅ Checklist Deployment

- [ ] aaPanel dan Nginx running normal.
- [ ] Root website mengarah ke direktori `/public`.
- [ ] PHP 8.4 dan socket fastcgi sudah terhubung.
- [ ] Cloudflare Tunnel aktif mengarah ke `http://localhost:80`.
- [ ] File `.env` sudah diisi kredensial production (`APP_DEBUG=false`).
- [ ] `php artisan key:generate` sudah dijalankan.
- [ ] Migrasi database selesai (`php artisan migrate --force`).
- [ ] Hak akses `storage` dan `bootstrap/cache` menggunakan user `www:www` (775/664).
- [ ] Build asset frontend berhasil (`npm run build`).
- [ ] **Cron Job aaPanel untuk Queue Worker Keep-Alive sudah aktif.**
- [ ] **Cron Job aaPanel untuk Laravel Scheduler sudah aktif.**
- [ ] Pengiriman pesan WhatsApp via Evolution API berhasil diuji.

---

## 🛡️ Lisensi

Dilindungi Hak Cipta © 2026 WABlast Management System.  
Seluruh hak cipta dan penggunaan aplikasi mengikuti ketentuan pemilik proyek.
