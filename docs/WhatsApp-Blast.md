# WhatsApp Blast dengan Baileys

## 📱 Tentang

WhatsApp Blast terintegrasi dengan Gerindra EMS menggunakan **Baileys** - library Node.js untuk WhatsApp Web API.

## ✅ Fitur

- ✅ Kirim pesan tunggal
- ✅ Kirim pesan bulk ke massa
- ✅ Notifikasi otomatis ke peserta event
- ✅ Template pesan
- ✅ Check nomor WhatsApp
- ✅ QR code authentication
- ✅ Session persistence

---

## 🚀 Quick Start

### 1. Start Baileys Server

```powershell
cd d:\laragon\www\Gerindra\whatsapp-server
npm start
```

Server akan berjalan di `http://localhost:3001`

### 2. Start Laravel

```powershell
cd d:\laragon\www\Gerindra
php artisan serve
```

### 3. Login & Scan QR

1. Buka `http://localhost:8000/whatsapp`
2. Scan QR code dengan WhatsApp di HP
3. Setelah connected, mulai kirim pesan!

---

## 📁 Struktur File

```
Gerindra/
├── whatsapp-server/          # Node.js Baileys server
│   ├── package.json
│   ├── server.js             # Express API server
│   └── auth_info/            # Session storage (created on first login)
│
├── app/
│   ├── Services/
│   │   └── WhatsAppService.php   # Laravel service
│   │
│   └── Http/Controllers/
│       └── WhatsAppController.php
│
├── resources/views/
│   └── whatsapp/
│       └── index.blade.php   # Dashboard UI
│
└── config/services.php       # Configuration
```

---

## ⚙️ Konfigurasi

### Environment Variables (.env)

```env
# WhatsApp Provider (baileys, fonnte, waha)
WHATSAPP_PROVIDER=baileys

# Baileys Server URL
BAILEYS_URL=http://localhost:3001

# Alternative: Fonnte (paid service)
# WHATSAPP_PROVIDER=fonnte
# FONNTE_TOKEN=your_fonnte_token
```

---

## 🔌 API Endpoints

### Baileys Server (Node.js - port 3001)

| Method | Endpoint        | Description                 |
| ------ | --------------- | --------------------------- |
| GET    | `/health`       | Health check                |
| GET    | `/status`       | Connection status           |
| GET    | `/qr`           | Get QR code (base64)        |
| POST   | `/send`         | Send message                |
| POST   | `/send-image`   | Send image with caption     |
| POST   | `/check-number` | Check if number on WhatsApp |
| POST   | `/bulk-send`    | Send bulk messages          |
| POST   | `/logout`       | Logout session              |

### Laravel Routes (port 8000)

| Method | Route                         | Description              |
| ------ | ----------------------------- | ------------------------ |
| GET    | `/whatsapp`                   | Dashboard                |
| GET    | `/whatsapp/health`            | Status check             |
| GET    | `/whatsapp/qr`                | Get QR code              |
| POST   | `/whatsapp/send`              | Send single message      |
| POST   | `/whatsapp/blast`             | Blast to massa           |
| POST   | `/whatsapp/event/{id}/notify` | Notify event registrants |

---

## 📤 Contoh Penggunaan

### Kirim Pesan Tunggal

```php
use App\Services\WhatsAppService;

$wa = new WhatsAppService();
$result = $wa->sendText('081234567890', 'Hello dari Gerindra!');

if ($result['success']) {
    echo "Pesan terkirim!";
}
```

### Bulk Send

```php
$phones = ['081234567890', '089876543210'];
$result = $wa->bulkSend($phones, 'Pengumuman penting!');

echo "Berhasil: {$result['success']}, Gagal: {$result['failed']}";
```

### Check Nomor

```php
$result = $wa->checkNumber('081234567890');
if ($result['exists']) {
    echo "Nomor terdaftar di WhatsApp";
}
```

---

## 🛠️ Troubleshooting

### Server tidak bisa diakses

```powershell
# Check jika server running
netstat -an | findstr "3001"

# Restart server
cd whatsapp-server
npm start
```

### QR Code tidak muncul

1. Pastikan Baileys server running
2. Check browser console untuk errors
3. Refresh halaman

### Session hilang setelah restart

Session disimpan di `whatsapp-server/auth_info/`. Folder ini akan otomatis dibuat dan menyimpan credential. Jangan hapus folder ini jika ingin keep session.

### Pesan tidak terkirim

1. Pastikan status "Terhubung"
2. Check format nomor (gunakan 08xxx atau 628xxx)
3. Pastikan nomor terdaftar di WhatsApp

---

## ⚠️ Best Practices

1. **Jeda antar pesan**: Default 2 detik untuk hindari block
2. **Jangan spam**: Batasi jumlah pesan per hari
3. **Gunakan template**: Pesan yang personal lebih efektif
4. **Monitor logs**: Check console Baileys untuk errors
5. **Backup session**: Simpan folder `auth_info` untuk recovery

---

## 📊 Monitoring

### Check Baileys Server Logs

```powershell
# Server akan print logs ke console
# 📤 Message sent to 081234567890
# ❌ Failed to send to 089876543210: ...
```

### Laravel Logs

```powershell
# Check Laravel logs
Get-Content storage\logs\laravel.log -Tail 50
```

---

**Version:** 1.0  
**Provider:** Baileys (Node.js)  
**Last Updated:** January 2026
