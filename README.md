# KadrGo - Telegram Recruitment Platform

![KadrGo](https://via.placeholder.com/800x200/0D9488/ffffff?text=KadrGo+-+Kadrlar+harakatda!)

KadrGo - bu Telegram ekotizimi ichida ishlashga mo'ljallangan zamonaviy ishga joylashish platformasi. Bu loyiha Laravel 11, Filament 3, Nutgram 4 va Vue 3 texnologiyalaridan foydalangan holda qurilgan.

## 🌟 Asosiy Xususiyatlar

### 💼 Ishchilar uchun
- ✅ Telegram bot orqali profil yaratish
- ✅ Vakansiya qidirish va filtrlash
- ✅ Geolokatsiya asosida yaqin ishlarni topish
- ✅ Vakansiyalarga ariza yuborish
- ✅ AI-powered savolnomalarni to'ldirish
- ✅ Ariza holatini kuzatish
- ✅ Real-time chat ish beruvchilar bilan
- ✅ Tavsiya etilgan vakansiyalar

### 🏢 Ish Beruvchilar uchun
- ✅ Vakansiya e'lon qilish
- ✅ Savolnoma konstruktori (6 ta savol turi)
- ✅ Kandidatlarni avtomatik skoringlash
- ✅ Kanban pipeline (7 bosqich)
- ✅ Kandidat taqqoslash
- ✅ Talent pool boshqaruvi
- ✅ Analytics va hisobotlar
- ✅ Xabar shablonlari

### ⚙️ Admin Panel
- ✅ Foydalanuvchi boshqaruvi
- ✅ Moderatsiya tizimi
- ✅ To'lov va obuna boshqaruvi
- ✅ Banner reklama tizimi
- ✅ Real-time statistika
- ✅ Hisobot va analitika

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 11
- **PHP**: 8.3+
- **Database**: MySQL 8.0+
- **Cache/Queue**: Redis 7+ / Database driver
- **Bot**: Nutgram 4.x
- **Admin Panel**: Filament 3
- **Authentication**: Laravel Sanctum

### Frontend (Mini App)
- **Framework**: Vue 3 (Composition API)
- **Build Tool**: Vite 5
- **CSS**: Tailwind CSS 3
- **State Management**: Pinia 2
- **Router**: Vue Router 4
- **Telegram**: WebApp SDK

### Integratsiyalar
- **To'lov**: Payme, Click, Uzum
- **SMS**: Eskiz.uz
- **AI**: Claude API / DeepSeek
- **Maps**: Yandex Maps

## 📦 O'rnatish

### Talablar
- PHP 8.3+
- Composer 2.x
- Node.js 18+
- MySQL 8.0+
- Redis (optional, for production)

### 1. Loyihani klonlash
```bash
git clone https://github.com/yourusername/kadrgo.git
cd kadrgo
```

### 2. Dependencies o'rnatish
```bash
# PHP dependencies
composer install

# JavaScript dependencies (for Mini App)
cd resources/miniapp
npm install
cd ../..
```

### 3. Environment sozlash
```bash
cp .env.example .env
php artisan key:generate
```

`.env` faylini tahrirlang va kerakli ma'lumotlarni kiriting:
- Database credentials
- Telegram bot token
- Payment gateway credentials
- Other service API keys

### 4. Database migratsiya
```bash
php artisan migrate --seed
```

### 5. Storage link
```bash
php artisan storage:link
```

### 6. Mini App build
```bash
cd resources/miniapp
cp .env.example .env
npm run build
```

## 🚀 Ishga Tushirish

### Development
```bash
# Laravel server
php artisan serve

# Queue worker
php artisan queue:work

# Horizon (optional)
php artisan horizon

# Mini App dev server
cd resources/miniapp
npm run dev
```

### Telegram Bot Setup
1. BotFather orqali bot yarating
2. Bot tokenni `.env` ga qo'shing
3. Webhook o'rnating:
```bash
php artisan telegram:webhook:set
```

4. Mini App yarating (@BotFather → /newapp)
5. Mini App URL ni o'rnating

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# With coverage
php artisan test --coverage
```

## 📁 Loyiha Strukturasi

```
kadrgo/
├── app/
│   ├── Enums/              # 11 ta enum
│   ├── Events/             # 6 ta event
│   ├── Filament/           # Admin + Recruiter panels
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/        # 14 Mini App controllers
│   │   │   ├── Api/Recruiter/ # 13 Recruiter controllers
│   │   │   └── Webhook/    # 3 webhook controllers
│   │   └── Middleware/     # 3 middleware
│   ├── Jobs/               # 6 ta job
│   ├── Models/             # 31 model
│   ├── Notifications/      # 5 ta notification
│   ├── Services/           # 11 ta service
│   └── Telegram/           # Bot handlers
├── database/
│   ├── migrations/         # 38 migration
│   └── seeders/            # 4 seeder
├── resources/
│   └── miniapp/            # Vue 3 Mini App
│       ├── src/
│       │   ├── components/ # 4 component
│       │   ├── composables/ # 3 composable
│       │   ├── stores/     # 4 Pinia store
│       │   └── views/      # 6 view
│       └── package.json
├── routes/
│   ├── api.php             # 95 Mini App + 36 Recruiter routes
│   ├── telegram.php        # Bot routes
│   └── web.php             # Filament routes
└── tests/
    ├── Feature/            # Feature tests
    └── Unit/               # Unit tests
```

## 🔐 Xavfsizlik

- ✅ Telegram initData HMAC-SHA256 validation
- ✅ Laravel Sanctum API token authentication
- ✅ Rate limiting (8 groups)
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS protection (Blade escaping)
- ✅ CSRF protection
- ✅ Payment webhook signature verification

## 📊 API Documentation

### Mini App API
- **Base URL**: `/api`
- **Authentication**: Sanctum token
- **Rate Limits**: 60-120 req/min

Key endpoints:
- `POST /api/auth/telegram` - Telegram authentication
- `GET /api/vacancies` - List vacancies
- `POST /api/applications` - Submit application
- `GET /api/search/nearby` - Nearby vacancies

### Recruiter API
- **Base URL**: `/api/recruiter`
- **Authentication**: Sanctum token
- **Rate Limit**: 120 req/min

Key endpoints:
- `GET /api/recruiter/dashboard` - Dashboard stats
- `GET /api/recruiter/vacancies` - Manage vacancies
- `POST /api/recruiter/questions` - Create questions

## 🚢 Production Deployment

See `DEPLOY.md` for detailed deployment instructions.

## 📈 Monitoring

- Laravel Telescope (dev/local)
- Laravel Horizon (queue monitoring)
- Sentry (error tracking - optional)

## 📝 License

Proprietary - KadrGo © 2026

## 📞 Support

- Email: support@kadrgo.uz
- Telegram: @kadrgo_support

---

**KadrGo - Kadrlar harakatda!** 🚀
