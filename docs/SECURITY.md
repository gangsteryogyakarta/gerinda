# Security Checklist - Gerindra EMS

## 🔒 Daftar Periksa Keamanan Aplikasi

**Versi:** 1.0  
**Terakhir Diperbarui:** 22 Januari 2026

---

## 1. Authentication & Authorization

### ✅ Implemented

- [x] Password hashing dengan bcrypt (default Laravel)
- [x] Session regeneration setelah login
- [x] Session invalidation saat logout
- [x] CSRF token di semua form
- [x] Login rate limiting (5 percobaan per menit)
- [x] Login attempt logging
- [x] Failed login logging
- [x] API token authentication (Sanctum)
- [x] Role-based access control (Spatie Permission)

### 📋 Production Checklist

- [ ] Ganti default admin password
- [ ] Aktifkan HTTPS di production
- [ ] Set `SESSION_SECURE_COOKIE=true`
- [ ] Set `SESSION_DOMAIN` ke domain production
- [ ] Verify Sanctum token expiry setting

---

## 2. Input Validation & Sanitization

### ✅ Implemented

- [x] Server-side validation di semua controller
- [x] NIK validation (16 digit)
- [x] Email validation
- [x] File upload validation (SecureFile rule)
- [x] Dangerous file extension blocking
- [x] PHP code injection detection in uploads
- [x] MIME type verification

### 📋 Best Practices Applied

- [x] Gunakan `$request->validate()` atau Form Requests
- [x] Whitelist allowed values (`in:value1,value2`)
- [x] Limit string lengths (`max:255`)
- [x] Validate foreign keys (`exists:table,column`)

---

## 3. SQL Injection Prevention

### ✅ Implemented

- [x] Eloquent ORM (parameterized queries)
- [x] Query Builder dengan bindings
- [x] No raw SQL tanpa prepared statements
- [x] Suspicious SQL pattern detection (logging)

### 📋 Verification

```php
// ✅ AMAN - Parameterized
User::where('email', $email)->first();

// ✅ AMAN - Binding
DB::select('SELECT * FROM users WHERE id = ?', [$id]);

// ❌ BERBAHAYA - Jangan gunakan
DB::select("SELECT * FROM users WHERE id = $id");
```

---

## 4. XSS Prevention

### ✅ Implemented

- [x] Blade auto-escaping (`{{ $variable }}`)
- [x] Content Security Policy header
- [x] X-XSS-Protection header
- [x] X-Content-Type-Options header
- [x] Suspicious XSS pattern logging

### 📋 Best Practices

```blade
<!-- ✅ AMAN - Auto-escaped -->
{{ $userInput }}

<!-- ⚠️ HATI-HATI - Unescaped, hanya untuk HTML yang dipercaya -->
{!! $trustedHtml !!}
```

---

## 5. CSRF Protection

### ✅ Implemented

- [x] CSRF token di semua POST/PUT/DELETE forms
- [x] Blade directive `@csrf`
- [x] API routes dengan Sanctum (token-based)

### 📋 Verification

Semua form harus memiliki:

```blade
<form method="POST">
    @csrf
    ...
</form>
```

---

## 6. Security Headers

### ✅ Implemented (SecurityHeaders Middleware)

| Header                    | Value                           | Purpose                  |
| ------------------------- | ------------------------------- | ------------------------ |
| X-Frame-Options           | SAMEORIGIN                      | Prevent clickjacking     |
| X-Content-Type-Options    | nosniff                         | Prevent MIME sniffing    |
| X-XSS-Protection          | 1; mode=block                   | XSS filter (legacy)      |
| Referrer-Policy           | strict-origin-when-cross-origin | Control referrer         |
| Content-Security-Policy   | [custom policy]                 | Script sources           |
| Strict-Transport-Security | max-age=31536000                | Force HTTPS (production) |
| Permissions-Policy        | camera=(self), ...              | Feature permissions      |

---

## 7. File Upload Security

### ✅ Implemented (SecureFile Rule)

- [x] Allowed MIME types whitelist
- [x] Dangerous extension blacklist
- [x] File size limits
- [x] Image validation (getimagesize)
- [x] PHP code injection check
- [x] Files stored outside public_html

### 📋 Dangerous Extensions Blocked

```
php, phtml, php3, php4, php5, php7, phps, phar, inc,
exe, bat, cmd, sh, bash, js, jsx, ts, tsx,
htaccess, htpasswd, asp, aspx, cgi, pl, py, rb, jar, war, svg
```

---

## 8. Rate Limiting

### ✅ Implemented

| Endpoint            | Limit            | Purpose             |
| ------------------- | ---------------- | ------------------- |
| API (general)       | 60/min per IP    | General protection  |
| Public registration | 10/min per IP    | Prevent spam        |
| NIK lookup          | 30/min per IP    | Prevent enumeration |
| Check-in            | 120/min per user | Allow fast scanning |
| Login               | 5/min per IP     | Prevent brute force |

---

## 9. Logging & Monitoring

### ✅ Implemented

- [x] Separate security log channel
- [x] Separate auth log channel
- [x] Failed login attempt logging
- [x] Successful login logging
- [x] Logout logging
- [x] Rate limit exceeded logging
- [x] Suspicious input pattern logging
- [x] Log rotation (30 days retention)

### 📋 Log Files

```
storage/logs/laravel.log     - Application errors
storage/logs/security.log    - Security events
storage/logs/auth.log        - Authentication events
```

---

## 10. Production Configuration

### 📋 .env Security Settings

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ems.gerindra.or.id

SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

LOG_LEVEL=error
```

### 📋 Server Configuration

- [ ] PHP `expose_php = Off`
- [ ] PHP `display_errors = Off`
- [ ] Nginx hide server version
- [ ] Block `.env` access
- [ ] Block `.git` access
- [ ] fail2ban configured

---

## 11. Sensitive Data Protection

### ✅ Implemented

- [x] Password tidak pernah di-log
- [x] Sensitive fields di-mask di log
- [x] API tokens hashed di database
- [x] NIK tidak ditampilkan lengkap di public

### 📋 Data Classification

| Data      | Sensitivity | Protection                |
| --------- | ----------- | ------------------------- |
| Passwords | Critical    | bcrypt hash, never logged |
| NIK       | High        | Partial masking in UI     |
| Email     | Medium      | Validated, not public     |
| Phone     | Medium      | Validated, opt-in sharing |
| Address   | Low-Medium  | Geocoded, not public      |

---

## 12. Dependency Security

### 📋 Regular Checks

```bash
# Check for vulnerable packages
composer audit

# Update dependencies
composer update --dry-run

# Check npm packages
npm audit
```

---

## 13. Backup & Recovery

### 📋 Implementation

- [ ] Daily database backups
- [ ] Encrypted backup storage
- [ ] Regular backup restoration tests
- [ ] 30-day backup retention
- [ ] Off-site backup copy

---

## 14. Incident Response

### 📋 Security Incident Checklist

1. [ ] Identify the breach/issue
2. [ ] Isolate affected systems
3. [ ] Preserve evidence (logs)
4. [ ] Assess impact
5. [ ] Notify stakeholders
6. [ ] Remediate vulnerability
7. [ ] Update security measures
8. [ ] Document lessons learned

---

## 15. Security Review Schedule

| Review Type       | Frequency | Next Due |
| ----------------- | --------- | -------- |
| Dependency audit  | Monthly   | -        |
| Access review     | Quarterly | -        |
| Penetration test  | Annually  | -        |
| Security training | Annually  | -        |

---

## 📁 Security Files Reference

```
app/
├── Http/
│   └── Middleware/
│       ├── SecurityHeaders.php      # HTTP security headers
│       ├── SanitizeInput.php        # Input sanitization
│       └── LogSuspiciousActivity.php # Threat detection
├── Rules/
│   └── SecureFile.php               # File upload validation
└── Providers/
    └── AppServiceProvider.php       # Rate limiter config

config/
└── logging.php                      # Security log channels

bootstrap/
└── app.php                          # Middleware registration
```

---

**Dokumen ini harus direview dan diperbarui setiap 3 bulan.**
