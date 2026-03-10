# KadrGo — Production Deploy Rejasi

## Umumiy Baho: 90/100 (Production ga tayyor, server kerak)

---

## TUZATILGAN MUAMMOLAR

### Xavfsizlik (Barcha kritik tuzatildi)

| # | Muammo | Holat | Fayl |
|---|--------|-------|------|
| 1.1 | Nutgram cache = array | ✅ Tuzatildi | config/nutgram.php → redis |
| 1.2 | Balance race condition | ✅ Tuzatildi | PaymentService.php → lockForUpdate() |
| 1.3 | Payme idempotency yo'q | ✅ Tuzatildi | PaymeService.php → status tekshiruvi |
| 1.4 | Avtorizatsiya yo'q | ✅ Tuzatildi | VacancyPolicy + ApplicationPolicy |
| 1.5 | Sanctum token cheksiz | ✅ Tuzatildi | config/sanctum.php → 43200 (30 kun) |
| 1.6 | HTTPS majburlash yo'q | ✅ Tuzatildi | AppServiceProvider → URL::forceScheme |
| 1.7 | Xavfsizlik headerlari yo'q | ✅ Tuzatildi | SecurityHeadersMiddleware (yangi) |
| 2.1 | Dev da Telegram auth bypass | ✅ Tuzatildi | TelegramWebAppAuth.php → barcha muhitda tekshirish |
| 2.2 | OTP brute force | ✅ Tuzatildi | AuthController → IP + phone rate limit |
| 2.3 | Fayl content tekshirilmaydi | ✅ Tuzatildi | FileUploadService → magic bytes |
| 2.4 | LIKE wildcard escape yo'q | ✅ Tuzatildi | SearchController, WebController, TalentPoolController, Recruiter/VacancyController |
| 2.5 | GeoService SQL injection | ✅ Tuzatildi | GeoService → column whitelist |
| 2.6 | Pagination limiti yo'q | ✅ Tuzatildi | Barcha controllerlarda max 100 |
| 2.7 | auth_date tekshiruvi yo'q | ✅ Tuzatildi | TelegramWebAppAuth → 24 soat limit |
| 2.8 | Telegram file hajm/tur yo'q | ✅ Tuzatildi | FileUploadService → extension whitelist + size limit |

### Performance (Barcha kritik tuzatildi)

| # | Muammo | Holat | Fayl |
|---|--------|-------|------|
| 3.1 | Database indexlar yo'q | ✅ Tuzatildi | add_production_indexes migratsiya |
| 3.2 | N+1 WebController regions | ✅ Tuzatildi | GROUP BY + 5 daqiqa cache |
| 3.3 | N+1 PaymentController | ✅ Tuzatildi | Batch query vacancy lookup |
| 3.4 | Dashboard 6 ta alohida query | ✅ Tuzatildi | 2 ta aggregate query |
| 3.5 | Analytics 4 ta alohida query | ✅ Tuzatildi | 1 ta aggregate query |
| 3.6 | Kategoriya/shahar cache yo'q | ✅ Tuzatildi | 24 soat Redis cache |
| 3.7 | Dashboard stats cache yo'q | ✅ Tuzatildi | 5 daqiqa cache |
| 3.8 | Mini App Leaflet eagerly loaded | ✅ Allaqachon lazy | MapView dynamic import |
| 3.9 | Skeleton loaders yo'q | ✅ Tuzatildi | SkeletonCard komponenti |

### Deploy Infra (Tayyor)

| # | Element | Holat | Fayl |
|---|---------|-------|------|
| 4.1 | .env production template | ✅ Tayyor | .env.production.example |
| 4.2 | Nginx konfiguratsiya | ✅ Tayyor | deploy/nginx.conf |
| 4.3 | Supervisor konfiguratsiya | ✅ Tayyor | deploy/supervisor.conf |
| 4.4 | Server o'rnatish skripti | ✅ Tayyor | deploy/server-setup.sh |
| 4.5 | Deploy skripti | ✅ Tayyor | deploy/deploy.sh |

---

## QOLGAN ISHLAR (Server kerak)

### Bosqich 1: Server sotib olish va sozlash
```
Minimal talablar:
  CPU:  2 core
  RAM:  4GB
  SSD:  20GB
  OS:   Ubuntu 22.04 LTS

Tavsiya etilgan (kadrgo.uz uchun):
  CPU:  4 core
  RAM:  8GB
  SSD:  100GB
  OS:   Ubuntu 22.04 LTS
```

### Bosqich 2: Serverga o'rnatish
```bash
# 1. Server o'rnatish
bash deploy/server-setup.sh

# 2. DNS sozlash (A yozuvi: kadrgo.uz -> server IP)

# 3. SSL sertifikat
certbot --nginx -d kadrgo.uz -d www.kadrgo.uz

# 4. Nginx konfiguratsiya
cp deploy/nginx.conf /etc/nginx/sites-available/kadrgo.uz
ln -s /etc/nginx/sites-available/kadrgo.uz /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

# 5. Supervisor
cp deploy/supervisor.conf /etc/supervisor/conf.d/kadrgo.conf

# 6. Birinchi deploy
bash deploy/deploy.sh first
```

### Bosqich 3: Post-deploy
- [ ] API kalitlarni yangilash (Telegram bot token, Anthropic key)
- [ ] To'lov provayderlar sozlash (Payme, Click, Uzum)
- [ ] Telegram webhook o'rnatish: `php artisan nutgram:hook:set`
- [ ] Sentry error tracking sozlash (ixtiyoriy)
- [ ] UptimeRobot monitoring qo'shish (ixtiyoriy)

### Bosqich 4: Test
- [ ] Barcha API endpointlarni test
- [ ] Telegram bot to'liq flow test
- [ ] Mini app to'liq flow test
- [ ] To'lov webhooklar test (sandbox)
- [ ] Backup/restore test

---

## ARXITEKTURA

```
┌──────────────────┐
│   Cloudflare     │  DDoS himoya, CDN, SSL (ixtiyoriy)
├──────────────────┤
│     Nginx        │  Reverse proxy, statik fayllar, gzip
├──────────────────┤
│   PHP 8.3-FPM    │  Laravel 11 + Nutgram v4
├──────────────────┤
│  MySQL 8 + Redis │  DB + Cache/Session/Queue
├──────────────────┤
│   Supervisor     │  Queue worker (2 process)
│   + Cron         │  Scheduler (har daqiqa)
└──────────────────┘
```

## DEPLOY BUYRUQLARI (Yangilash)
```bash
# Yangi kodni deploy qilish
bash deploy/deploy.sh update
```

Bu buyruq avtomatik ravishda:
1. Maintenance mode yoqadi
2. Git pull qiladi
3. Composer install (no-dev)
4. Migratsiya ishga tushiradi
5. Frontend build qiladi (panel + miniapp)
6. Keshlarni yangilaydi
7. Queue workerlarni restart qiladi
8. Maintenance mode o'chiradi
