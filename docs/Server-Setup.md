# Server Setup Guide - Gerindra EMS

## 📋 Overview

| Component       | Specification          |
| --------------- | ---------------------- |
| **OS**          | Ubuntu 22.04 LTS       |
| **PHP**         | 8.3 with OPcache + JIT |
| **Web Server**  | Nginx 1.24+            |
| **Database**    | MySQL 8.0              |
| **Cache/Queue** | Redis 7.x              |
| **SSL**         | Cloudflare Origin Cert |

---

## 1. Initial Server Setup

### 1.1 Update System

```bash
sudo apt update && sudo apt upgrade -y
```

### 1.2 Create Deploy User

```bash
# Create non-root user
sudo adduser deploy
sudo usermod -aG sudo deploy

# Setup SSH key
su - deploy
mkdir ~/.ssh
chmod 700 ~/.ssh
nano ~/.ssh/authorized_keys  # Paste your public key
chmod 600 ~/.ssh/authorized_keys
```

### 1.3 Firewall Setup

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

---

## 2. Install PHP 8.3

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.3 with extensions
sudo apt install -y php8.3-fpm php8.3-cli php8.3-common \
    php8.3-mysql php8.3-zip php8.3-gd php8.3-mbstring \
    php8.3-curl php8.3-xml php8.3-bcmath php8.3-redis \
    php8.3-intl php8.3-soap php8.3-imagick

# Verify installation
php -v
```

### 2.1 Configure PHP-FPM

```bash
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

```ini
[www]
user = deploy
group = deploy
listen = /run/php/php8.3-fpm.sock
listen.owner = deploy
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500

; Performance settings
request_terminate_timeout = 60s
```

### 2.2 Configure OPcache

```bash
sudo nano /etc/php/8.3/fpm/conf.d/10-opcache.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1

; JIT Configuration
opcache.jit=1255
opcache.jit_buffer_size=128M
```

```bash
sudo systemctl restart php8.3-fpm
```

---

## 3. Install Nginx

```bash
sudo apt install nginx -y

# Remove default site
sudo rm /etc/nginx/sites-enabled/default
```

### 3.1 Create Application Configuration

```bash
sudo cp deploy/nginx/gerindra.conf /etc/nginx/sites-available/gerindra.conf
sudo ln -s /etc/nginx/sites-available/gerindra.conf /etc/nginx/sites-enabled/

# Test and reload
sudo nginx -t
sudo systemctl reload nginx
```

---

## 4. Install MySQL 8.0

```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation

# Create database and user
sudo mysql -e "CREATE DATABASE gerindra_ems CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'gerindra'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';"
sudo mysql -e "GRANT ALL PRIVILEGES ON gerindra_ems.* TO 'gerindra'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
```

### 4.1 Optimize MySQL

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Add at the end:

```ini
# Performance Tuning
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

query_cache_type = 1
query_cache_size = 128M
query_cache_limit = 2M

max_connections = 200
thread_cache_size = 50

# Slow query log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

```bash
sudo systemctl restart mysql
```

---

## 5. Install Redis

```bash
sudo apt install redis-server -y

# Configure Redis
sudo nano /etc/redis/redis.conf
```

Change/add:

```ini
supervised systemd
maxmemory 512mb
maxmemory-policy allkeys-lru
```

```bash
sudo systemctl restart redis
sudo systemctl enable redis

# Test
redis-cli ping
```

---

## 6. Install Composer

```bash
cd ~
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
rm composer-setup.php

# Verify
composer --version
```

---

## 7. Install Node.js (for asset compilation)

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs

# Verify
node -v
npm -v
```

---

## 8. Setup Application Directory

```bash
# Create directory structure
sudo mkdir -p /var/www/gerindra
sudo mkdir -p /var/www/gerindra/releases
sudo mkdir -p /var/www/gerindra/shared/storage
sudo mkdir -p /var/www/gerindra/shared/storage/logs
sudo mkdir -p /var/www/gerindra/shared/storage/framework/{sessions,views,cache}

# Set ownership
sudo chown -R deploy:deploy /var/www/gerindra

# Set permissions
chmod -R 775 /var/www/gerindra/shared/storage
```

---

## 9. Setup Supervisor (Queue Workers)

```bash
sudo apt install supervisor -y

# Copy configuration
sudo cp deploy/supervisor/gerindra.conf /etc/supervisor/conf.d/

# Start supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start gerindra-worker:*
```

---

## 10. Setup Cron (Scheduler)

```bash
# Edit crontab for deploy user
crontab -e
```

Add:

```bash
* * * * * cd /var/www/gerindra/current && php artisan schedule:run >> /dev/null 2>&1
```

---

## 11. SSL Certificate (Cloudflare)

### 11.1 Generate Origin Certificate

1. Go to Cloudflare Dashboard → SSL/TLS → Origin Server
2. Create Certificate (15 years)
3. Save as:
    - `/etc/ssl/certs/cloudflare.pem` (Certificate)
    - `/etc/ssl/private/cloudflare.key` (Private Key)

```bash
# Secure private key
sudo chmod 600 /etc/ssl/private/cloudflare.key
```

---

## 12. Environment Configuration

```bash
# Copy environment file
cp /var/www/gerindra/current/.env.production /var/www/gerindra/shared/.env

# Edit with actual values
nano /var/www/gerindra/shared/.env
```

Important variables to configure:

- `APP_KEY` - Generate with `php artisan key:generate --show`
- `DB_PASSWORD` - Your MySQL password
- `REDIS_PASSWORD` - If Redis has password
- `WA_GATEWAY_TOKEN` - WhatsApp gateway token

---

## 13. First Deployment

```bash
cd /var/www/gerindra

# Clone repository to releases
git clone git@github.com:your-org/gerindra-ems.git releases/$(date +%Y%m%d%H%M%S)

# Link shared storage
ln -nfs /var/www/gerindra/shared/storage releases/$(ls releases | tail -1)/storage
ln -nfs /var/www/gerindra/shared/.env releases/$(ls releases | tail -1)/.env

# Install dependencies
cd releases/$(ls releases | tail -1)
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Run migrations
php artisan migrate --force

# Optimize
php artisan optimize
php artisan cache:warmup

# Create current symlink
ln -nfs /var/www/gerindra/releases/$(ls /var/www/gerindra/releases | tail -1) /var/www/gerindra/current

# Reload services
sudo systemctl reload php8.3-fpm
sudo supervisorctl restart gerindra-worker:*
```

---

## 14. Monitoring Setup

### 14.1 Install Netdata

```bash
bash <(curl -Ss https://my-netdata.io/kickstart.sh)
```

### 14.2 Health Check Cron

```bash
# Add to crontab
*/5 * * * * curl -sf http://localhost/health || echo "Health check failed" | mail -s "EMS Health Alert" admin@gerindra.or.id
```

---

## 15. Backup Setup

```bash
# Copy backup script
cp scripts/backup-db.sh /home/deploy/
chmod +x /home/deploy/backup-db.sh

# Setup daily backup cron
crontab -e
```

Add:

```bash
0 2 * * * /home/deploy/backup-db.sh >> /var/log/backup.log 2>&1
```

---

## 16. Security Hardening

### 16.1 Fail2ban

```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
```

### 16.2 Unattended Upgrades

```bash
sudo apt install unattended-upgrades -y
sudo dpkg-reconfigure -plow unattended-upgrades
```

### 16.3 Disable root login

```bash
sudo nano /etc/ssh/sshd_config
```

```ini
PermitRootLogin no
PasswordAuthentication no
```

```bash
sudo systemctl restart sshd
```

---

## Quick Reference

### Service Commands

```bash
# Nginx
sudo systemctl {start|stop|restart|reload} nginx

# PHP-FPM
sudo systemctl {start|stop|restart} php8.3-fpm

# MySQL
sudo systemctl {start|stop|restart} mysql

# Redis
sudo systemctl {start|stop|restart} redis

# Supervisor
sudo supervisorctl {status|restart all|stop all}
```

### Log Locations

```bash
# Application
/var/www/gerindra/shared/storage/logs/

# Nginx
/var/log/nginx/

# PHP-FPM
/var/log/php8.3-fpm.log

# MySQL
/var/log/mysql/

# Redis
/var/log/redis/
```

### Deployment

```bash
cd /var/www/gerindra/current
./deploy.sh
```

### Rollback

```bash
cd /var/www/gerindra/current
./rollback.sh
```

---

**Document Version:** 1.0  
**Last Updated:** January 2026
