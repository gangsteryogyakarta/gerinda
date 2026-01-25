#!/bin/bash

# ==============================================================================
# Gerindra EMS - Automated Server Provisioning Script (Non-Interactive Fix)
# Support: Ubuntu 22.04 LTS on IDCloudHost
# Run as root
# ==============================================================================

set -e

# SET DEBIAN_FRONTEND to NONINTERACTIVE to avoid prompts
export DEBIAN_FRONTEND=noninteractive

# Configuration
APP_NAME="gerindra"
APP_DIR="/var/www/${APP_NAME}"
PHP_VERSION="8.3"
DB_NAME="gerindra_ems"
DB_USER="gerindra"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log_info() { echo -e "${GREEN}[INFO]${NC} $1"; }

# Check if root
if [ "$EUID" -ne 0 ]; then
  echo "Please run as root"
  exit 1
fi

# 0. Kill any stuck apt processes
log_info "Cleaning up previous failed installs..."
killall apt apt-get 2>/dev/null || true
rm /var/lib/apt/lists/lock 2>/dev/null || true
rm /var/cache/apt/archives/lock 2>/dev/null || true
rm /var/lib/dpkg/lock* 2>/dev/null || true
dpkg --configure -a || true

# 1. Update System
log_info "Updating system packages..."
apt-get update -y
# Option Dpkg::Options::="--force-confdef" and "--force-confold" keeps old configs without prompting
apt-get upgrade -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold"
apt-get install -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" curl git zip unzip software-properties-common ufw acl

# 2. Setup Firewall
log_info "Configuring Firewall..."
ufw allow OpenSSH
ufw allow 'Nginx Full'
# ufw enable # Use caution enabling this in script.

# 3. Install PHP 8.3
log_info "Installing PHP ${PHP_VERSION}..."
add-apt-repository ppa:ondrej/php -y
apt-get update -y
apt-get install -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" \
    php${PHP_VERSION}-fpm php${PHP_VERSION}-cli php${PHP_VERSION}-common \
    php${PHP_VERSION}-mysql php${PHP_VERSION}-zip php${PHP_VERSION}-gd php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-curl php${PHP_VERSION}-xml php${PHP_VERSION}-bcmath php${PHP_VERSION}-redis \
    php${PHP_VERSION}-intl php${PHP_VERSION}-soap php${PHP_VERSION}-imagick

# Configure PHP-FPM
log_info "Configuring PHP-FPM..."
FPM_CONF="/etc/php/${PHP_VERSION}/fpm/pool.d/www.conf"
sed -i "s/pm.max_children = 5/pm.max_children = 50/" $FPM_CONF
sed -i "s/pm.start_servers = 2/pm.start_servers = 10/" $FPM_CONF
sed -i "s/pm.min_spare_servers = 1/pm.min_spare_servers = 5/" $FPM_CONF
sed -i "s/pm.max_spare_servers = 3/pm.max_spare_servers = 20/" $FPM_CONF

# OPcache
OPCACHE_INI="/etc/php/${PHP_VERSION}/fpm/conf.d/10-opcache.ini"
cat > $OPCACHE_INI <<EOF
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
opcache.jit=1255
opcache.jit_buffer_size=128M
EOF

systemctl restart php${PHP_VERSION}-fpm

# 4. Install Nginx
log_info "Installing Nginx..."
apt-get install -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" nginx

# 5. Install MySQL
log_info "Installing MySQL..."
apt-get install -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" mysql-server

DB_PASS=$(openssl rand -base64 12)
log_info "Creating Database & User..."
# Wait for MySQL to start
sleep 5
mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

log_info "MySQL User: ${DB_USER}"
log_info "MySQL Pass: ${DB_PASS}"
echo "MySQL Password: ${DB_PASS}" > /root/mysql_credentials.txt

# 6. Install Redis
log_info "Installing Redis..."
apt-get install -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" redis-server
sed -i "s/supervised no/supervised systemd/" /etc/redis/redis.conf
systemctl restart redis

# 7. Install Composer
log_info "Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 8. Install Node.js
log_info "Installing Node.js..."
curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
apt-get install -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" nodejs

# 9. Setup Setup Directories & User
log_info "Setting up directories..."
if ! id "deploy" &>/dev/null; then
    useradd -m -s /bin/bash deploy
    usermod -aG sudo deploy
    usermod -aG www-data deploy
fi

mkdir -p ${APP_DIR}
mkdir -p ${APP_DIR}/releases
mkdir -p ${APP_DIR}/shared/storage
mkdir -p ${APP_DIR}/shared/storage/logs
mkdir -p ${APP_DIR}/shared/storage/framework/{sessions,views,cache}
mkdir -p ${APP_DIR}/shared/storage/app/public

chown -R deploy:deploy ${APP_DIR}
chmod -R 775 ${APP_DIR}/shared/storage

# 10. Install Supervisor
log_info "Installing Supervisor..."
apt-get install -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" supervisor

log_info "==========================================================="
log_info "Provisioning Complete!"
log_info "==========================================================="
