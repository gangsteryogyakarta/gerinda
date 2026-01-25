# Production Deployment Guide - Gerindra EMS

## 🚀 Quick Deployment

### Automated Deployment (Recommended)

```bash
cd /var/www/gerindra/current
./deploy.sh
```

### Manual Deployment

```bash
# 1. Pull latest changes
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Run migrations
php artisan migrate --force

# 4. Optimize
php artisan optimize
php artisan cache:warmup --force

# 5. Restart services
sudo systemctl reload php8.3-fpm
sudo supervisorctl restart gerindra-worker:*
```

---

## 📋 Deployment Checklist

### Pre-Deployment

- [ ] All tests passing on CI
- [ ] Code reviewed and approved
- [ ] Database migration tested on staging
- [ ] Changelog updated
- [ ] Team notified

### Deployment

- [ ] Run `./deploy.sh`
- [ ] Verify health check: `curl https://ems.gerindra.or.id/health`
- [ ] Test critical flows (login, registration, check-in)
- [ ] Check error logs: `tail -f storage/logs/laravel.log`

### Post-Deployment

- [ ] Monitor for 15 minutes
- [ ] Check Sentry for new errors
- [ ] Verify queue processing
- [ ] Update deployment log

---

## 🔄 Zero-Downtime Deployment Flow

```
1. Clone to new release directory
   └── releases/20260122054500/

2. Install dependencies (Composer + NPM)

3. Create symlinks to shared resources
   ├── storage/ → shared/storage/
   └── .env → shared/.env

4. Run migrations

5. Cache config, routes, views

6. Atomic switch: current → new release

7. Reload PHP-FPM (graceful)

8. Restart queue workers

9. Health check

10. Cleanup old releases (keep 5)
```

---

## 🔙 Rollback Procedure

### Quick Rollback

```bash
./rollback.sh
```

### Manual Rollback

```bash
# List available releases
ls -la /var/www/gerindra/releases/

# Switch to previous release
ln -nfs /var/www/gerindra/releases/PREVIOUS_RELEASE /var/www/gerindra/current

# Reload services
sudo systemctl reload php8.3-fpm
sudo supervisorctl restart gerindra-worker:*
```

### Rollback with Database

```bash
# If migration needs reversal
php artisan migrate:rollback --step=1

# Then switch release
./rollback.sh
```

---

## 🔍 Monitoring Commands

### Health Check

```bash
# Quick check
php artisan system:health

# Detailed via HTTP
curl https://ems.gerindra.or.id/health/detailed
```

### Queue Status

```bash
# Check queue size
php artisan queue:monitor redis:default,redis:high --max=100

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Cache Status

```bash
# Clear cache
php artisan cache:clear

# Warm up cache
php artisan cache:warmup --force
```

### Logs

```bash
# Application logs
tail -f storage/logs/laravel.log

# Security logs
tail -f storage/logs/security.log

# Auth logs
tail -f storage/logs/auth.log
```

---

## 📊 Performance Checks

### Response Time

```bash
# Test homepage
curl -w "@curl-format.txt" -o /dev/null -s https://ems.gerindra.or.id/

# Test API
curl -w "@curl-format.txt" -o /dev/null -s https://ems.gerindra.or.id/api/v1/events
```

### Database Queries

```bash
# Check slow queries
sudo tail -f /var/log/mysql/slow.log
```

### Memory Usage

```bash
# PHP-FPM processes
ps aux | grep php-fpm | awk '{sum += $6} END {print sum/1024 " MB"}'

# Redis memory
redis-cli info memory | grep used_memory_human
```

---

## 🔧 Troubleshooting

### 500 Internal Server Error

```bash
# Check permissions
sudo chown -R deploy:deploy storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Check logs
tail -100 storage/logs/laravel.log
tail -100 /var/log/nginx/error.log
```

### Queue Not Processing

```bash
# Check supervisor status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart gerindra-worker:*

# Check for failed jobs
php artisan queue:failed
```

### Cache Issues

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan optimize
php artisan cache:warmup --force
```

### Database Connection Issues

```bash
# Test connection
mysql -u gerindra -p -e "SELECT 1"

# Check MySQL status
sudo systemctl status mysql

# Check connection pool
mysql -u gerindra -p -e "SHOW STATUS LIKE 'Threads_connected'"
```

### Redis Issues

```bash
# Test connection
redis-cli ping

# Check memory
redis-cli info memory

# Monitor commands
redis-cli monitor
```

---

## 📝 Environment Variables for Deployment

### Required Secrets (GitHub Actions)

```yaml
SSH_HOST: your-server-ip
SSH_USER: deploy
SSH_KEY: (private SSH key)
STAGING_PATH: /var/www/gerindra-staging
PRODUCTION_PATH: /var/www/gerindra
TELEGRAM_TOKEN: (for notifications)
TELEGRAM_CHAT_ID: (for notifications)
```

### .env Production Values

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ems.gerindra.or.id

# Performance
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error

# Security
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

---

## 📆 Maintenance Windows

### Recommended Schedule

- **Minor Updates**: Daily, 02:00 - 04:00 WIB
- **Major Updates**: Sunday, 02:00 - 06:00 WIB
- **Database Maintenance**: Monthly, Sunday 03:00 WIB

### Maintenance Mode

```bash
# Enable (with secret bypass)
php artisan down --secret="bypass-secret-token"

# Access during maintenance
https://ems.gerindra.or.id/bypass-secret-token

# Disable
php artisan up
```

---

## 📞 Emergency Contacts

| Role            | Name | Contact |
| --------------- | ---- | ------- |
| DevOps Lead     | -    | -       |
| Backend Lead    | -    | -       |
| Hosting Support | -    | -       |

---

## 📚 Related Documentation

- [Server Setup Guide](./Server-Setup.md)
- [Security Checklist](./SECURITY.md)
- [Optimization Checklist](../Optimization-Checklist.md)
- [API Documentation](./API.md)

---

**Document Version:** 1.0  
**Last Updated:** January 2026
