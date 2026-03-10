#!/bin/bash
# ============================================================
# KadrGo — Server O'rnatish Skripti (Ubuntu 22.04 LTS)
# Root sifatida ishga tushiring: bash server-setup.sh
# ============================================================

set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() { echo -e "${GREEN}[SETUP]${NC} $1"; }
warn() { echo -e "${YELLOW}[!]${NC} $1"; }

# Root tekshirish
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Bu skript root sifatida ishga tushirilishi kerak!${NC}"
    exit 1
fi

log "KadrGo server o'rnatish boshlanmoqda..."

# ─── 1. Tizimni yangilash ───
log "1/8 — Tizim yangilanmoqda..."
apt update && apt upgrade -y

# ─── 2. PHP 8.3 ───
log "2/8 — PHP 8.3 o'rnatilmoqda..."
apt install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt update

apt install -y \
    php8.3-fpm \
    php8.3-cli \
    php8.3-mysql \
    php8.3-redis \
    php8.3-gd \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-bcmath \
    php8.3-intl \
    php8.3-readline

# PHP-FPM sozlamalari
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 15M/' /etc/php/8.3/fpm/php.ini
sed -i 's/post_max_size = .*/post_max_size = 20M/' /etc/php/8.3/fpm/php.ini
sed -i 's/memory_limit = .*/memory_limit = 256M/' /etc/php/8.3/fpm/php.ini
sed -i 's/max_execution_time = .*/max_execution_time = 60/' /etc/php/8.3/fpm/php.ini

systemctl restart php8.3-fpm

# ─── 3. MySQL 8 ───
log "3/8 — MySQL 8 o'rnatilmoqda..."
apt install -y mysql-server

# MySQL xavfsizligini sozlash
mysql -e "CREATE DATABASE IF NOT EXISTS kadrgo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'kadrgo_user'@'localhost' IDENTIFIED BY 'KUCHLI_PAROL_QOYING';"
mysql -e "GRANT ALL PRIVILEGES ON kadrgo.* TO 'kadrgo_user'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
warn "MySQL parolni o'zgartiring: mysql -e \"ALTER USER 'kadrgo_user'@'localhost' IDENTIFIED BY 'yangi_parol';\""

# ─── 4. Redis ───
log "4/8 — Redis o'rnatilmoqda..."
apt install -y redis-server

# Redis xavfsizlik
sed -i 's/# requirepass .*/requirepass REDIS_PAROL_QOYING/' /etc/redis/redis.conf
sed -i 's/bind .*/bind 127.0.0.1 ::1/' /etc/redis/redis.conf
systemctl restart redis-server
warn "Redis parolni o'zgartiring: /etc/redis/redis.conf"

# ─── 5. Nginx ───
log "5/8 — Nginx o'rnatilmoqda..."
apt install -y nginx

# Rate limit zona
cat > /etc/nginx/conf.d/rate-limit.conf << 'EOF'
limit_req_zone $binary_remote_addr zone=telegram:10m rate=30r/s;
limit_req_zone $binary_remote_addr zone=api:10m rate=60r/s;
EOF

systemctl restart nginx

# ─── 6. Supervisor ───
log "6/8 — Supervisor o'rnatilmoqda..."
apt install -y supervisor
systemctl enable supervisor

# ─── 7. Node.js 20 LTS ───
log "7/8 — Node.js 20 o'rnatilmoqda..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# ─── 8. Yordamchi dasturlar ───
log "8/8 — Yordamchi dasturlar..."
apt install -y \
    git \
    curl \
    unzip \
    certbot \
    python3-certbot-nginx \
    ufw

# Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ─── Firewall (UFW) ───
log "Firewall sozlanmoqda..."
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Nginx Full'
ufw --force enable

# ─── SSL sertifikat ───
log "SSL sertifikat olish..."
warn "DNS A yozuvini oldin sozlang, keyin bu buyruqni ishga tushiring:"
echo "  certbot --nginx -d kadrgo.uz -d www.kadrgo.uz --non-interactive --agree-tos -m admin@kadrgo.uz"

# ─── Papka yaratish ───
mkdir -p /var/www/kadrgo
chown -R www-data:www-data /var/www/kadrgo

# ─── Xulosa ───
log "================================================"
log "Server o'rnatish YAKUNLANDI!"
log ""
log "Keyingi qadamlar:"
log "  1. DNS A yozuvini sozlang (kadrgo.uz -> server IP)"
log "  2. SSL sertifikat oling:"
log "     certbot --nginx -d kadrgo.uz -d www.kadrgo.uz"
log "  3. Nginx konfiguratsiyani nusxalang:"
log "     cp deploy/nginx.conf /etc/nginx/sites-available/kadrgo.uz"
log "     ln -s /etc/nginx/sites-available/kadrgo.uz /etc/nginx/sites-enabled/"
log "     rm /etc/nginx/sites-enabled/default"
log "     nginx -t && systemctl reload nginx"
log "  4. Supervisor konfiguratsiyani nusxalang:"
log "     cp deploy/supervisor.conf /etc/supervisor/conf.d/kadrgo.conf"
log "  5. Deploy qiling:"
log "     bash deploy/deploy.sh first"
log "================================================"
