#!/bin/bash
# ============================================================
# KadrGo — Production Deploy Script
# Ishlatish: bash deploy.sh [first|update]
# ============================================================

set -euo pipefail

APP_DIR="/var/www/kadrgo"
BRANCH="main"
PHP="php8.3"

# Ranglar
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() { echo -e "${GREEN}[DEPLOY]${NC} $1"; }
warn() { echo -e "${YELLOW}[OGOHLANTIRISH]${NC} $1"; }
error() { echo -e "${RED}[XATO]${NC} $1"; exit 1; }

# ─── Birinchi marta deploy ───
first_deploy() {
    log "Birinchi marta deploy boshlanmoqda..."

    # 1. Repo klonlash
    if [ ! -d "$APP_DIR" ]; then
        log "Repository klonlanmoqda..."
        git clone --branch "$BRANCH" --single-branch https://github.com/YOUR_USERNAME/kadrgo.git "$APP_DIR"
    fi

    cd "$APP_DIR"

    # 2. Composer
    log "Composer paketlari o'rnatilmoqda..."
    composer install --no-dev --optimize-autoloader --no-interaction

    # 3. .env fayl
    if [ ! -f .env ]; then
        log ".env fayl yaratilmoqda..."
        cp .env.production.example .env
        $PHP artisan key:generate
        warn ".env faylni to'ldiring va deploy.sh update ni qayta ishga tushiring!"
        exit 0
    fi

    # 4. Storage link
    log "Storage link yaratilmoqda..."
    $PHP artisan storage:link

    # 5. Migratsiya
    log "Migratsiyalar ishga tushirilmoqda..."
    $PHP artisan migrate --force

    # 6. Frontend build
    log "Frontend buildlar..."
    npm ci --production=false
    npm run build

    # Mini App build
    cd resources/miniapp
    npm ci --production=false
    npm run build
    cd "$APP_DIR"

    # 7. Cache
    log "Keshlar yaratilmoqda..."
    $PHP artisan config:cache
    $PHP artisan route:cache
    $PHP artisan view:cache
    $PHP artisan event:cache

    # 8. Huquqlar
    log "Fayl huquqlari sozlanmoqda..."
    chown -R www-data:www-data "$APP_DIR"
    chmod -R 755 "$APP_DIR"
    chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

    # 9. Supervisor
    log "Supervisor qayta yuklanmoqda..."
    supervisorctl reread
    supervisorctl update
    supervisorctl start "kadrgo-worker:*"

    # 10. Crontab
    log "Crontab sozlanmoqda..."
    (crontab -l 2>/dev/null | grep -v "kadrgo"; echo "* * * * * cd $APP_DIR && $PHP artisan schedule:run >> /dev/null 2>&1") | crontab -

    # 11. Nginx
    log "Nginx qayta yuklanmoqda..."
    nginx -t && systemctl reload nginx

    # 12. Telegram webhook
    log "Telegram webhook o'rnatilmoqda..."
    $PHP artisan nutgram:hook:set

    log "================================================"
    log "Birinchi deploy MUVAFFAQIYATLI yakunlandi!"
    log "Sayt: https://kadrgo.uz"
    log "================================================"
}

# ─── Yangilash deploy ───
update_deploy() {
    log "Yangilash deploy boshlanmoqda..."
    cd "$APP_DIR"

    # 1. Maintenance mode
    log "Texnik profilaktika rejimi..."
    $PHP artisan down --retry=30 --refresh=5

    # 2. Git pull
    log "Yangi kod yuklanmoqda..."
    git pull origin "$BRANCH"

    # 3. Composer
    log "Composer paketlari yangilanmoqda..."
    composer install --no-dev --optimize-autoloader --no-interaction

    # 4. Migratsiya
    log "Migratsiyalar..."
    $PHP artisan migrate --force

    # 5. Frontend build
    log "Frontend build..."
    npm ci --production=false
    npm run build

    cd resources/miniapp
    npm ci --production=false
    npm run build
    cd "$APP_DIR"

    # 6. Keshlarni tozalash va qayta yaratish
    log "Keshlar yangilanmoqda..."
    $PHP artisan config:cache
    $PHP artisan route:cache
    $PHP artisan view:cache
    $PHP artisan event:cache
    $PHP artisan cache:clear

    # 7. Queue restart
    log "Queue workerlar qayta ishga tushirilmoqda..."
    $PHP artisan queue:restart
    supervisorctl restart "kadrgo-worker:*"

    # 8. Huquqlar
    chown -R www-data:www-data "$APP_DIR"
    chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

    # 9. Maintenance off
    log "Sayt qayta ochilmoqda..."
    $PHP artisan up

    log "================================================"
    log "Yangilash MUVAFFAQIYATLI yakunlandi!"
    log "$(date '+%Y-%m-%d %H:%M:%S')"
    log "================================================"
}

# ─── Asosiy ───
case "${1:-}" in
    first)
        first_deploy
        ;;
    update)
        update_deploy
        ;;
    *)
        echo "Ishlatish: bash deploy.sh [first|update]"
        echo ""
        echo "  first  — Birinchi marta serverga deploy"
        echo "  update — Mavjud kodni yangilash"
        exit 1
        ;;
esac
