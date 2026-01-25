# WAHA (WhatsApp HTTP API) Setup Guide

## 📱 Tentang WAHA

WAHA adalah WhatsApp HTTP API self-hosted yang memungkinkan mengirim pesan WhatsApp secara programatik.

**Fitur:**

- ✅ Kirim pesan teks
- ✅ Kirim gambar dengan caption
- ✅ Kirim dokumen/file
- ✅ Bulk messaging
- ✅ Check nomor WhatsApp
- ✅ Multi-session
- ✅ Webhook untuk receive messages

---

## 🚀 Quick Setup

### Step 1: Install Docker Desktop

1. Download dari: https://www.docker.com/products/docker-desktop/
2. Install dan restart komputer
3. Jalankan Docker Desktop
4. Verifikasi dengan: `docker --version`

### Step 2: Jalankan WAHA

```powershell
# Navigasi ke folder project
cd d:\laragon\www\Gerindra

# Jalankan WAHA dengan Docker Compose
docker compose -f docker-compose.waha.yml up -d

# Atau dengan docker run langsung
docker run -d --name gerindra-waha -p 3000:3000 -e WHATSAPP_API_KEY=gerindra-secret-key-2026 devlikeapro/waha:latest
```

### Step 3: Akses Dashboard

1. Buka browser: http://localhost:3000
2. Gunakan API Key: `gerindra-secret-key-2026`

### Step 4: Scan QR Code

1. Akses: http://localhost:3000/api/screenshot?session=default
2. Atau gunakan Laravel dashboard: `/whatsapp`
3. Scan QR dengan WhatsApp di HP Anda

---

## ⚙️ Konfigurasi

### Environment Variables (.env)

```env
# WAHA Configuration
WAHA_URL=http://localhost:3000
WAHA_API_KEY=gerindra-secret-key-2026
WAHA_SESSION=gerindra
```

### Docker Compose (docker-compose.waha.yml)

File sudah tersedia di root project dengan konfigurasi:

- Port: 3000
- API Key: gerindra-secret-key-2026
- Session: gerindra
- Auto-restart on failure

---

## 📡 API Endpoints

### Session Management

| Method | Endpoint                  | Description          |
| ------ | ------------------------- | -------------------- |
| GET    | `/whatsapp`               | Dashboard WhatsApp   |
| GET    | `/whatsapp/health`        | Check WAHA status    |
| GET    | `/whatsapp/qr`            | Get QR code for auth |
| POST   | `/whatsapp/session/start` | Start session        |
| POST   | `/whatsapp/session/stop`  | Stop session         |
| POST   | `/whatsapp/logout`        | Logout WhatsApp      |

### Messaging

| Method | Endpoint                      | Description                 |
| ------ | ----------------------------- | --------------------------- |
| POST   | `/whatsapp/send`              | Send single message         |
| POST   | `/whatsapp/blast`             | Blast to massa              |
| POST   | `/whatsapp/event/{id}/notify` | Notify event registrants    |
| POST   | `/whatsapp/check-number`      | Check if number on WhatsApp |

---

## 💬 Usage Examples

### Send Single Message

```javascript
fetch("/whatsapp/send", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
    },
    body: JSON.stringify({
        phone: "081234567890",
        message: "Hello from Gerindra EMS!",
    }),
});
```

### Blast to All Massa

```javascript
fetch("/whatsapp/blast", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
    },
    body: JSON.stringify({
        filter: "all", // or 'active', 'province'
        province_id: null, // required if filter is 'province'
        limit: 100, // optional
        message: "Pengumuman penting dari DPD Gerindra...",
    }),
});
```

### Notify Event Registrants

```javascript
fetch("/whatsapp/event/1/notify", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
    },
    body: JSON.stringify({
        status: "confirmed", // or 'all', 'pending'
        message: "Reminder: Event besok jam 09:00!",
    }),
});
```

---

## 🔧 Docker Commands

```powershell
# Start WAHA
docker compose -f docker-compose.waha.yml up -d

# Stop WAHA
docker compose -f docker-compose.waha.yml down

# View logs
docker logs gerindra-waha -f

# Restart WAHA
docker compose -f docker-compose.waha.yml restart

# Check status
docker ps | Select-String waha
```

---

## ⚠️ Important Notes

### Rate Limiting

- Default delay: 2 seconds between messages
- Avoid sending too fast to prevent WhatsApp ban
- Recommended: max 100-200 messages per batch

### Session Persistence

- Sessions stored in Docker volume `waha_sessions`
- Will persist across container restarts
- Logout only if needed (removes session)

### Best Practices

1. **Test with small batch first** before large blast
2. **Use background jobs** for bulk messaging (already implemented)
3. **Monitor queue** with `php artisan queue:work`
4. **Check logs** for failed deliveries

---

## 🛠️ Troubleshooting

### WAHA not accessible

```powershell
# Check if container is running
docker ps

# Check container logs
docker logs gerindra-waha

# Restart container
docker restart gerindra-waha
```

### QR Code not showing

1. Session mungkin sudah authenticated
2. Check `/whatsapp/health` untuk status
3. Logout dan scan ulang jika perlu

### Messages not sending

1. Pastikan session status "WORKING"
2. Check format nomor (harus include country code)
3. Verifikasi nomor terdaftar di WhatsApp

### Job Queue not processing

```powershell
# Start queue worker
php artisan queue:work

# Or with specific queue
php artisan queue:work redis --queue=default
```

---

## 📊 Monitoring

### Check Last Blast Result

```php
$result = cache()->get('bulk_whatsapp_last_result');
// Returns: ['total' => 100, 'success' => 95, 'failed' => 5, 'completed_at' => '...']
```

### View Logs

```powershell
# Laravel logs
Get-Content storage\logs\laravel.log -Tail 50

# WAHA container logs
docker logs gerindra-waha --tail 50
```

---

**Document Version:** 1.0  
**Last Updated:** January 2026
