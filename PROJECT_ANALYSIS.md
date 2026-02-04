# Analisa Project Gerindra Event Management System (EMS)

Berikut adalah hasil analisa mendalam mengenai arsitektur aplikasi, konfigurasi GitHub, dan setup VPS di IDCloudHost.

## 1. Arsitektur Aplikasi

Aplikasi ini dibangun menggunakan **Laravel 11** dengan dukungan teknologi modern untuk performa tinggi dan skalabilitas.

- **Backend**: Laravel 11 (PHP 8.3)
- **Frontend**: Modern Javascript Stack (via Vite & NPM), kemungkinan Vue.js atau React (berdasarkan struktur build).
- **Database**: MySQL 8.0
- **Cache & Queue**: Redis (digunakan untuk session driver, cache, dan antrian job).
- **Web Server**: Nginx.
- **Fitur Khusus**:
    - **WAHA (WhatsApp HTTP API)**: Terintegrasi untuk fitur WhatsApp Blast/Notifikasi. Berjalan di port `3000` dan di-proxy oleh Nginx melalui path `/waha/`.
    - **Supervisor**: Mengelola worker queue untuk proses background (email, WA blast).

## 2. Analisa GitHub & CI/CD

Repository terhubung ke GitHub dengan URL: `https://github.com/gangsteryogyakarta/gerinda`

### Automated Workflow (`.github/workflows/ci-cd.yml`)

Sistem menggunakan GitHub Actions untuk otomatisasi penuh:

1.  **Testing**: Menjalankan Unit Test (PHPUnit) dengan service MySQL & Redis setiap ada push.
2.  **Quality Control**: PHPStan (Static Analysis) dan PHP-CS-Fixer (Code Style).
3.  **Security Audit**: Memeriksa celah keamanan pada dependensi Composer & NPM.
4.  **Build Assets**: Mengkompilasi asset frontend (Vite/NPM) dan menyiapkannya sebagai artifact.
5.  **Deployment**:
    - **Staging**: Otomatis deploy ke `staging.ems-gerindra.or.id` saat push ke branch `main`.
    - **Production**: Manual Trigger (harus di-klik manual di GitHub Actions) ke `ems.gerindra.or.id`.
    - **Metode**: Menggunakan SSH Action untuk masuk ke VPS dan menjalankan script `./deploy.sh`.

## 3. Analisa VPS (IDCloudHost)

Berdasarkan file konfigurasi `nginx_gerindra.conf` dan `deploy.sh`:

- **IP Address**: `27.112.78.114`
- **Domain**:
    - Utama: `gerindradiy.com` (Note: Ada perbedaan dengan config CI/CD yang mengarah ke `ems.gerindra.or.id`. Kemungkinan server melayani kedua domain atau konfigurasi CI/CD perlu disesuaikan).
- **Lokasi App**: `/var/www/gerindra`
- **Strategi Deployment**: **Zero Downtime Deployment**.
    - Menggunakan struktur folder `releases/` (versi timestamped) dan `current` (symlink).
    - Ini memastikan saat user mengakses web, tidak ada error maintenance saat proses deploy berlangsung.
- **Security Nginx**:
    - Rate Limiting: `/login` (5 req/detik), `/api` (30 req/detik).
    - Memblokir akses ke file sensitif (`.env`, `.git`).
    - Header keamanan ketat (XSS-Protection, NoSniff).

## Rekomendasi

1.  **Sinkronisasi Domain**: Konfirmasi kembali apakah domain produksi yang aktif adalah `gerindradiy.com` atau `ems.gerindra.or.id`. Jika `gerindradiy.com`, update file `.github/workflows/ci-cd.yml` bagian production environment url.
2.  **Worker Monitoring**: Pastikan Supervisor di VPS sudah dipastikan berjalan (`supervisorctl status`) untuk menangani job antrian.
