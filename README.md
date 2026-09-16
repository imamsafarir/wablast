# Panduan Instalasi, Deployment aaPanel, Cloudflare Tunnel & Update Git

Dokumen ini berisi panduan teknis lengkap untuk memasang aplikasi **WABlast (Laravel + Evolution API)** di server **aaPanel (Local Server / VPS)**, mengkonfigurasi **Cloudflare Tunnel Zero Trust**, serta perintah **Terminal Git** untuk memperbarui aplikasi secara otomatis dari repository GitHub.

---

## 🚀 Fitur Utama & Arsitektur

- **Laravel 11 & PHP 8.4** dengan sistem otentikasi multi-tenant (Superadmin, Admin, Pengguna).
- **Evolution API Gateway** untuk pengiriman WhatsApp massal (Blast) & Single Message secara asynchronous via Queue Worker.
- **Bypass / Compatibility Cloudflare Tunnel Zero Trust**: Bebas dari masalah _Mixed Content_ HTTPS dan deteksi IP Pengguna asli di balik proxy Cloudflare.
- **Super-Fast Queue Worker Auto-Trigger**: Proses blast berjalan di latar belakang tanpa membebankan server.

---

## 🛠️ Langkah 1: Persyaratan Server aaPanel

Sebelum memulai, pastikan server aaPanel Anda telah terpasang software berikut melalui **aaPanel App Store**:

1. **LNMP / LAMP Stack**:
    - **Nginx** (versi 1.20+)
    - **PHP 8.2 / 8.3 / 8.4** (Sangat direkomendasikan PHP 8.4)
    - **MySQL 8.0 / MariaDB 10.6+**
2. **PHP Extensions** (Buka _aaPanel -> PHP Manager -> Install Extensions_):
    - `bcmath`, `curl`, `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo_mysql`, `xml`, `zip`, `redis` (opsional).
3. **PHP Disabled Functions** (Buka _aaPanel -> PHP Manager -> Disabled Functions_):
    - Hapus fungsi `proc_open`, `popen`, `exec`, `shell_exec`, dan `system` dari daftar disabled function agar Laravel Queue Worker & Scheduler dapat berjalan.
4. **Composer**:
    - Pastikan Composer versi 2.x terpasang (`composer --version`).
5. **Node.js & NPM** (via aaPanel PM2 Manager / Terminal):
    - Node.js v18+ atau v20+.

---

## 📁 Langkah 2: Deployment & Clone Repository di aaPanel

Buka **aaPanel Terminal** atau SSH ke server Anda:

```bash
# 1. Masuk ke direktori web aaPanel
cd /www/wwwroot

# 2. Clone Repository dari GitHub (ganti URL repository Anda)
git clone https://github.com/USERNAME/wablast.git wablast

# 3. Masuk ke folder proyek
cd /www/wwwroot/wablast

# 4. Install Dependensi PHP
composer install --no-dev --optimize-autoloader

# 5. Salin file environment .env
cp .env.example .env

# 6. Generate Application Key
php artisan key:generate
```

---

## ⚙️ Langkah 3: Konfigurasi Database & Environment (.env)

Edit file `.env` melalui **aaPanel Files Manager** (`/www/wwwroot/wablast/.env`) atau via terminal:

```env
APP_NAME="WABlast"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://wablast.domainanda.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wablast_db
DB_USERNAME=wablast_user
DB_PASSWORD=password_db_anda

# Evolution API Gateway Configuration
EVOLUTION_API_URL=https://evolution.domainanda.com
EVOLUTION_API_APIKEY=GlobalAPIKeyAnda
WA_INSTANCE_NAME=wablast
```

Jalankan Migrasi Database dan Seeder Akun Default:

```bash
# Migration & Seeder Akun Utama (Superadmin, Admin, Pengguna)
php artisan migrate:fresh --seed --force

# Storage Link untuk Logo, Favicon, dan Gambar Blast
php artisan storage:link

# Optimasi Cache Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔐 Akun Default Setelah Seeder (`php artisan db:seed`)

| Role           | Username     | Email                    | Password Default |
| -------------- | ------------ | ------------------------ | ---------------- |
| **Superadmin** | `superadmin` | `superadmin@wablast.com` | `password`       |
| **Admin**      | `admin`      | `admin@wablast.com`      | `password`       |
| **Pengguna**   | `pengguna`   | `user@wablast.com`       | `password`       |

---

## 🌐 Langkah 4: Pengaturan Domain & Nginx di aaPanel

1. Buka **aaPanel -> Website -> Add Site**.
2. Masukkan Domain/Subdomain Anda (misal: `wablast.domainanda.com`).
3. Set **Site Directory** ke `/www/wwwroot/wablast`.
4. Set **Running Directory** ke `/public` _(Sangat Penting!)_.
5. Edit **Nginx Configuration** pada website tersebut di aaPanel:

```nginx
server {
    listen 80;
    server_name wablast.domainanda.com;
    root /www/wwwroot/wablast/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # Bypass Cloudflare Proxy Real IP Header
    set_real_ip_from 0.0.0.0/0;
    real_ip_header CF-Connecting-IP;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi-84.sock; # Sesuaikan versi PHP aaPanel Anda
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## ☁️ Langkah 5: Konfigurasi Cloudflare Tunnel Zero Trust

Jika Anda menjalankan aplikasi di **Server Lokal (Localhost/Home Server)** tanpa IP Publik:

1. Buka Dashboard **Cloudflare Zero Trust** -> **Networks** -> **Tunnels**.
2. Buat Tunnel Baru (misal: `wablast-local-tunnel`).
3. Install `cloudflared` agent di server aaPanel Anda:
    ```bash
    # Contoh Linux AMD64
    curl -L --output cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
    sudo dpkg -i cloudflared.deb
    sudo cloudflared service install TOKEN_CLOUDFLARE_ZERO_TRUST_ANDA
    ```
4. Di Dashboard Cloudflare Tunnel, tambahkan **Public Hostname**:
    - **Subdomain**: `wablast`
    - **Domain**: `domainanda.com`
    - **Service Type**: `HTTP`
    - **URL**: `localhost:80` (atau IP lokal server aaPanel Anda, misal `192.168.1.100:80`).
5. **Konfigurasi SSL Cloudflare**:
    - Di Dashboard Cloudflare SSL/TLS, pilih mode **Full** atau **Flexible**. Aplikasi Laravel WABlast telah terkonfigurasi secara otomatis mem-bypass HTTPS scheme melalui `trustProxies(at: '*')` sehingga tidak akan terjadi error _Too Many Redirects_ atau _Mixed Content HTTP/HTTPS_.

---

## ⏱️ Langkah 6: Pengaturan Supervisor / Queue Worker & Cron Job di aaPanel

Agar pengiriman WhatsApp Blast berjalan 24/7 di latar belakang:

### A. Pengaturan Cron Job Laravel (aaPanel -> Cron)

- **Type**: Shell Script
- **Name**: `WABlast Laravel Schedule`
- **Period**: `Every Minute` (`* * * * *`)
- **Script Text**:
    ```bash
    /usr/bin/php /www/wwwroot/wablast/artisan schedule:run >> /dev/null 2>&1
    ```

### B. Pengaturan Supervisor (aaPanel App Store -> Supervisor Manager)

- **Name**: `wablast-worker`
- **Run User**: `www`
- **Run Directory**: `/www/wwwroot/wablast`
- **Start Command**:
    ```bash
    php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    ```

---

## 🔄 Langkah 7: Perintah Update Otomatis via Terminal aaPanel (Git Update Command)

Setiap kali Anda push pembaruan kode dari local ke GitHub, Anda dapat memperbarui aplikasi di server aaPanel secara langsung dengan **1 Perintah Terminal**:

### Script One-Liner Update:

Jalankan perintah berikut di Terminal aaPanel pada direktori `/www/wwwroot/wablast`:

```bash
git pull origin main && composer install --no-dev --optimize-autoloader && php artisan migrate --force && php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan optimize
```

### Penjelasan Perintah Update:

1. `git pull origin main`: Mengunduh pembaruan kode terbaru dari repository GitHub.
2. `composer install --no-dev --optimize-autoloader`: Memperbarui pustaka PHP.
3. `php artisan migrate --force`: Menjalankan migrasi database baru secara otomatis.
4. `php artisan config:clear && php artisan route:clear && php artisan view:clear`: Membersihkan seluruh cache lama.
5. `php artisan optimize`: Membangun kembali cache konfigurasi & rute berkinerja tinggi.

---

## 🛡️ Hak Cipta & Lisensi

Dilindungi Hak Cipta © 2026 WABlast Management System.
