# Walkthrough - Deployment & Fixes Gerindra EMS

Berhasil melakukan deployment perbaikan untuk masalah **Timeout** saat generate tiket dan **Error 419 (Page Expired)** di domain `gerindradiy.com`.

## Perubahan yang Dilakukan

### 1. Optimasi Batch Ticket Generation

- **File**: `app/Jobs/GenerateBatchTicketsJob.php`
- **Perbaikan**:
    - Menambahkan `set_time_limit(0)` untuk mencegah PHP memutus proses di tengah jalan.
    - Meningkatkan limit memori menjadi `512M`.
    - Menggunakan _chunking_ yang lebih kecil (20 data per batch) untuk stabilitas server.
    - Eager loading data (`with(['event', 'massa'])`) untuk menghilangkan masalah N+1 Query yang membuat proses lambat.

### 2. Perbaikan Sesi & Keamanan (Fix Error 419)

- **File**: `.env` (Server)
- **Perbaikan**:
    - `APP_URL`: Diupdate ke `https://gerindradiy.com`.
    - `SESSION_DOMAIN`: Diset ke `.gerindradiy.com` agar cookies valid secara konsisten.
    - `SESSION_SECURE_COOKIE`: Diset ke `true` karena server menggunakan HTTPS.
    - `APP_DEBUG`: Diset ke `false` (Mode Produksi).

### 3. Otomatisasi SSH & Deployment

- **Script Baru**:
    - `deploy_prod.bat`: Script interaktif untuk menjalankan deploy di server.
    - `ssh_prod.bat`: Shortcut untuk login SSH ke server (Root).
- **Update**: Perbaikan password hint (`4pp5GERINDRA`) di semua script pembantu.

### 4. Perbaikan Login Error 500

- **Masalah**: Halaman login mengembalikan status 500 setelah deployment.
- **Penyebab**: Masalah perizinan (permissions) pada folder `storage` dan `bootstrap/cache`.
- **Perbaikan**:
    - Mereset ownership folder ke `www-data`.
    - Membersihkan dan membangun ulang cache konfigurasi (`php artisan config:cache`).
    - Verifikasi: Halaman login sekarang aktif (**200 OK**).

### 6. Perbaikan Print All Tickets (Error 500)

- **Masalah**: Halaman `/print-tickets` mengembalikan Error 500 pada event dengan banyak peserta (500+).
- **Penyebab**: Batas memori PHP (128MB) terlampaui saat men-generate PDF berukuran besar.
- **Perbaikan**:
    - Meningkatkan `memory_limit` menjadi `512M` di `EventController@printAllTickets`.
    - Menghapus batas waktu eksekusi (`set_time_limit(0)`).
    - Verifikasi: PDF tiket sekarang bisa di-download meskipun datanya banyak.

### 7. Fitur Cetak Tiket Skalabel (Background Processing)

- **Fitur Baru**: Sistem antrian untuk mencetak tiket masal tanpa timeout.
- **Komponen**:
    - Tabel `print_jobs` untuk tracking status.
    - Dashboard `Print History` untuk monitoring.
    - Template PDF baru (2x2 Grid, Full A4, Header Merah).
- **Benefit**: Bisa mencetak ribuan tiket tanpa membebani server karena diproses per batch (50 tiket) di background.
- **Regenerate**: Tombol Generate akan otomatis menghapus file lama dan membuat ulang dengan desain terbaru.

## Hasil Verifikasi

- **Deployment**: Status **SUCCESS** (Exit Code 0).
- **Login**: Status **UP** (200 OK).
- **Server Config**: `APP_DEBUG=false` & `SESSION_DOMAIN=.gerindradiy.com`.
- **Services**: PHP-FPM di-reload dan Supervisor queue workers telah di-restart.
- **Print All Tickets**: Berhasil (Download PDF 550+ tiket lancar dengan limit 512MB).
- **Background Print**: Sukses generate batch, layout 4 tiket/halaman rapi.

---
