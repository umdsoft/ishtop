# IshTop - Production Deployment Guide

Bu qo'llanma IshTop loyihasini production serverga deploy qilish bo'yicha to'liq ko'rsatmalar beradi.

## 📋 Server Requirements

### Minimum Requirements
- **OS**: Ubuntu 22.04 LTS or higher
- **CPU**: 2 cores
- **RAM**: 4 GB
- **Disk**: 20 GB SSD
- **PHP**: 8.3+
- **MySQL**: 8.0+
- **Redis**: 7.0+ (recommended)
- **Nginx**: 1.20+

### PHP Extensions
```bash
sudo apt install php8.3-cli php8.3-fpm php8.3-mysql php8.3-redis \
  php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip \
  php8.3-gd php8.3-intl php8.3-bcmath
```

## 🔧 Server Setup

### 1. Update System
```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install Nginx
```bash
sudo apt install nginx -y
sudo systemctl enable nginx
sudo systemctl start nginx
```

### 3. Install MySQL
```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
```

Create database:
```sql
CREATE DATABASE ishtop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ishtop_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON ishtop.* TO 'ishtop_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Install Redis
```bash
sudo apt install redis-server -y
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

### 5. Install Node.js & NPM
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

### 6. Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## 📁 Application Deployment

### 1. Clone Repository
```bash
cd /var/www
sudo git clone https://github.com/yourusername/ishtop.git
sudo chown -R www-data:www-data ishtop
cd ishtop
```

### 2. Install Dependencies
```bash
# PHP dependencies (production)
composer install --optimize-autoloader --no-dev

# Mini App dependencies
cd resources/miniapp
npm install
npm run build
cd ../..
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```env
APP_NAME=IshTop
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ishtop.uz

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=ishtop
DB_USERNAME=ishtop_user
DB_PASSWORD=strong_password_here

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Add Telegram, Payment, SMS configs
```

### 4. Database Migration
```bash
php artisan migrate --force
php artisan db:seed --force
```

### 5. Storage & Permissions
```bash
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 6. Optimize Application
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## 🌐 Nginx Configuration

Create `/etc/nginx/sites-available/ishtop.uz`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name ishtop.uz www.ishtop.uz;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ishtop.uz www.ishtop.uz;
    root /var/www/ishtop/public;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/ishtop.uz/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ishtop.uz/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';" always;

    index index.php;

    charset utf-8;

    # Max upload size
    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/ishtop.uz /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 🔒 SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d ishtop.uz -d www.ishtop.uz
```

Auto-renewal:
```bash
sudo certbot renew --dry-run
```

## 🔄 Queue Workers (Supervisor)

Create `/etc/supervisor/conf.d/ishtop-worker.conf`:

```ini
[program:ishtop-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ishtop/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/ishtop/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ishtop-worker:*
```

## ⏰ Cron Jobs

Add to crontab:
```bash
sudo crontab -e -u www-data
```

Add line:
```
* * * * * cd /var/www/ishtop && php artisan schedule:run >> /dev/null 2>&1
```

## 📊 Laravel Horizon (Optional)

Install Horizon:
```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

Create `/etc/supervisor/conf.d/ishtop-horizon.conf`:

```ini
[program:ishtop-horizon]
process_name=%(program_name)s
command=php /var/www/ishtop/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/ishtop/storage/logs/horizon.log
stopwaitsecs=3600
```

## 🔄 Deployment Script

Create `/var/www/ishtop/deploy.sh`:

```bash
#!/bin/bash

set -e

echo "Starting deployment..."

# Pull latest code
git pull origin main

# Install/update dependencies
composer install --optimize-autoloader --no-dev

# Build Mini App
cd resources/miniapp
npm install
npm run build
cd ../..

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart services
sudo supervisorctl restart ishtop-worker:*

echo "Deployment completed successfully!"
```

Make executable:
```bash
chmod +x deploy.sh
```

## 🔍 Monitoring & Logs

### View Logs
```bash
tail -f storage/logs/laravel.log
tail -f storage/logs/worker.log
```

### Queue Status
```bash
php artisan queue:work --once  # Test
php artisan horizon:status      # If using Horizon
```

### Supervisor Status
```bash
sudo supervisorctl status
```

## 🔐 Security Checklist

- [ ] APP_DEBUG=false in production
- [ ] Strong database passwords
- [ ] SSL certificate installed
- [ ] Firewall configured (UFW)
- [ ] Regular backups configured
- [ ] Log rotation configured
- [ ] Rate limiting enabled
- [ ] CORS configured properly
- [ ] API tokens secured
- [ ] Webhook signatures verified

## 🔄 Backup Strategy

### Database Backup
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/ishtop"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

mysqldump -u ishtop_user -p ishtop > $BACKUP_DIR/db_$DATE.sql
gzip $BACKUP_DIR/db_$DATE.sql

# Keep only last 7 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete
```

Add to cron:
```
0 2 * * * /path/to/backup-script.sh
```

## 📞 Troubleshooting

### Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Reset Permissions
```bash
sudo chown -R www-data:www-data /var/www/ishtop
sudo chmod -R 775 storage bootstrap/cache
```

### Restart Services
```bash
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
sudo systemctl restart redis-server
sudo supervisorctl restart all
```

---

## 🎉 Post-Deployment

1. Test all API endpoints
2. Verify Telegram bot webhook
3. Test Mini App functionality
4. Check payment webhooks
5. Monitor logs for errors
6. Run smoke tests

**Production URL**: https://ishtop.uz
**Admin Panel**: https://ishtop.uz/admin
**Recruiter Panel**: https://ishtop.uz/recruiter

Good luck! 🚀
