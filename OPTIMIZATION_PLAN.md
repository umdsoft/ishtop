# KadrGo - Professional Code Audit & Optimization Plan

## Loyiha haqida qisqacha

**KadrGo** — Telegram Mini App asosidagi ish bozori platformasi. Laravel 11 (backend) + Vue 3 (frontend) + Nutgram (Telegram bot). Ish qidiruvchilar va ish beruvchilarni bog'laydi. To'lov tizimlari (Payme, Click, Uzum), AI matching, questionnaire scoring, geolokatsiya va boshqa enterprise xususiyatlar mavjud.

---

# I. XAVFSIZLIK MUAMMOLARI (Security) — CRITICAL

## 1.1 Vacancy update/delete uchun authorization yo'q
**Fayl:** `app/Http/Controllers/Api/VacancyController.php:114-144`

`update()` va `destroy()` metodlarida vacancy egasi tekshirilmaydi. Har qanday autentifikatsiya qilingan foydalanuvchi **boshqa odamning vakansiyasini** o'zgartirishi yoki o'chirishi mumkin.

## 1.2 Application stage/withdraw uchun authorization yo'q
**Fayl:** `app/Http/Controllers/Api/ApplicationController.php:70-84`

`updateStage()` va `withdraw()` metodlari ownership tekshirmaydi.

## 1.3 Click webhook imzo tekshiruvi yo'q
**Fayl:** `app/Http/Controllers/Webhook/ClickController.php`

PaymeController va UzumController imzo/autentifikatsiya tekshiradi, lekin ClickController tekshirmaydi. Soxta webhook orqali to'lovlarni "muvaffaqiyatli" deb belgilash mumkin.

## 1.4 Non-production muhitda Telegram auth bypass
**Fayl:** `app/Http/Controllers/Api/AuthController.php:201-203`, `TelegramWebAppAuth.php:52-53`

Staging muhit public bo'lsa, har kim har qanday foydalanuvchi sifatida login qilishi mumkin.

## 1.5 OTP kodi non-production response'da ochiq
**Fayl:** `AuthController.php:133-135`, `Recruiter/AuthController.php:153-156`

## 1.6 Admin paroli source code'da hardcoded
**Fayl:** `database/seeders/AdminSeeder.php`
```php
'password' => 'Umidbek19952812' // CRITICAL!
```

## 1.7 Sanctum token muddatsiz
**Fayl:** `config/sanctum.php` — `expiration: null`

Moliyaviy operatsiyalar mavjud loyihada tokenlar hech qachon tugamaydi.

## 1.8 ProfileController validation yo'q
**Fayl:** `app/Http/Controllers/Api/ProfileController.php:17-29`

`workerUpdate()` hech qanday validation qilmaydi.

---

# II. ARXITEKTURA MUAMMOLARI (Architecture) — HIGH

## 2.1 Form Request klasslar yo'q
Butun loyihada inline `$request->validate()` ishlatiladi. Bu:
- Validation qoidalarini bir necha controllerda takrorlaydi
- Controllerlarni haddan tashqari kattalashtiradi
- Qayta ishlatib bo'lmaydigan logika

**Yechim:** Har bir endpoint uchun `FormRequest` klass yaratish.

## 2.2 API Resource/Transformer yo'q
Barcha controllerlar `response()->json($model)` qaytaradi:
- Ichki database ustunlari API'da ochiq
- Response formati nostandart
- Ustun nomi o'zgarsa, API buziladi

**Yechim:** `JsonResource` klasslar yaratish.

## 2.3 Policy/Gate authorization yo'q
Authorization inline tekshiruvlar bilan amalga oshiriladi. Laravel Policy tizimi ishlatilmagan:
- Authorization logikasi controllerlar bo'ylab tarqalgan
- Tekshiruvlarni unutish oson (1.1 va 1.2 ko'ring)

**Yechim:** `VacancyPolicy`, `ApplicationPolicy` va boshqa Policy klasslar yaratish.

## 2.4 Service interfeyslari yo'q
Barcha servicelar konkret klasslar, interfeys yoki abstraktsiya yo'q:
- Test yozish qiyin (mock qilib bo'lmaydi)
- Implementatsiyani almashtirish mumkin emas
- DIP (Dependency Inversion) buziladi

## 2.5 Database tranzaktsiyalar yo'q
Ko'p qadam operatsiyalar tranzaktsiyasiz:
- `ReviewController::store` — review yaratadi, keyin ratingni yangilaydi
- `ApplicationController::store` — application yaratadi, keyin vacancy counterni increment qiladi
- `ChatController::send` — message yaratadi, keyin chatni update qiladi

---

# III. DRY BUZILISHLARI (Code Duplication) — HIGH

## 3.1 Backend DRY buzilishlari

| # | Takrorlangan kod | Joylashuv | Necha marta |
|---|---|---|---|
| 1 | Telegram initData verification | AuthController, TelegramWebAppAuth, Recruiter/AuthController | 3x |
| 2 | User response serialization | AuthController: telegram() va telegramToken() | 2x |
| 3 | Employer profile tekshiruvi | 6 ta controllerda ~15 joyda | 15x |
| 4 | `$vacancyIds = Vacancy::where('employer_id', ...)->pluck('id')` | Dashboard, Analytics controllerlar | 8x |
| 5 | Vacancy validation qoidalari | Api/VacancyController, Recruiter/VacancyController | 2x |
| 6 | Haversine formulasi | GeoService, Vacancy::scopeNearby, WorkerProfile::scopeNearby | 3x |
| 7 | Payment service webhook pattern | PaymeService, ClickService, UzumService | 3x |
| 8 | Active employer auto-fix | Recruiter/AuthController — 4 joyda | 4x |

## 3.2 Frontend DRY buzilishlari

| # | Takrorlangan kod | Joylashuv | Necha marta |
|---|---|---|---|
| 1 | `formatSalary()` | VacancyDetailView, ApplicationsView, MapView, MyVacanciesView, VacancyCard | 5x |
| 2 | `timeAgo()` | NotificationsView, MyVacanciesView, VacancyCard | 3x |
| 3 | `getInitial()` | VacancyDetailView, ApplicationsView, ProfileView, VacancyCard | 4x |
| 4 | `formatNumber()` | VacancyDetailView, PostVacancyView, ProfileView | 3x |
| 5 | `formatDate()` | VacancyDetailView, ApplicationsView | 2x |
| 6 | Categories API chaqiruvi | 5 ta view'da | 5x |
| 7 | Cities API chaqiruvi | 3 ta view'da | 3x |
| 8 | `no-scrollbar` CSS | 3 ta view'da | 3x |

---

# IV. SOLID TAMOYILLARI BUZILISHLARI

## 4.1 Single Responsibility Principle (SRP)

| Controller | Mas'uliyat soni | Fayl |
|---|---|---|
| `Api/VacancyController` | 8+ (CRUD, payment, AI tarjima, candidates, pricing) | VacancyController.php |
| `Api/AuthController` | 7 (auth, OTP, profile, crypto verification) | AuthController.php |
| `Recruiter/AuthController` | 8 (login, register, OTP, Telegram, employer profile) | Recruiter/AuthController.php |
| `PaymentService` | 5 (create, complete, fail, balance, service activation) | PaymentService.php |

## 4.2 Open/Closed Principle (OCP)

- `PaymentService::activateService()` — hardcoded `match` statement. Yangi payment type qo'shish uchun service'ni o'zgartirish kerak
- `ScoringService::scoreAnswer()` — yangi question type qo'shish uchun o'zgartirish kerak
- `MatchingService::CATEGORY_KEYWORDS` — kategoriyalar hardcoded

## 4.3 Dependency Inversion Principle (DIP)

- `app(VacancyService::class)` — container'dan to'g'ridan-to'g'ri olish constructor injection o'rniga
- `request()->ip()` — Service ichida HTTP request'ga to'g'ridan-to'g'ri murojaat
- `AiService` — Anthropic API'ga to'g'ridan-to'g'ri bog'langan, interfeys yo'q

---

# V. PERFORMANCE MUAMMOLARI

## 5.1 Backend Performance

| # | Muammo | Joylashuv | Ta'sir |
|---|---|---|---|
| 1 | **N+1 Query**: Har bir vacancy uchun `countRecommendedCandidates()` | Recruiter/VacancyController:59-62 | Har sahifada 20+ qo'shimcha so'rov |
| 2 | **N+1 Query**: Har bir chat uchun `unreadCountFor()` | ChatController:28-31 | Har sahifada 20+ qo'shimcha so'rov |
| 3 | **Full table scan**: MatchingService barcha worker'larni xotiraga yuklaydi | MatchingService:77-83 | O(n) — katta bazada sezilarli sekinlashish |
| 4 | **Duplicate queries**: Review avg + count alohida so'rovlar | ReviewController:39-40 | 2 so'rov 1 o'rniga |
| 5 | **activeSubscription()** har chaqiruvda so'rov yuboradi | User model, SubscriptionLimitService | Bir requestda bir necha marta |
| 6 | **Missing eager loading** ko'p joylarda | VacancyController:55, QuestionnaireService:38 | Lazy loading N+1 |

## 5.2 Frontend Performance

| # | Muammo | Joylashuv | Ta'sir |
|---|---|---|---|
| 1 | **`<keep-alive>` yo'q** — tab almashganda barcha state yo'qoladi | App.vue/router | Har tab almashishda to'liq re-fetch |
| 2 | **Categories/Cities cache yo'q** — har sahifada qayta yuklanadi | 5+ view | 5-8 keraksiz API chaqiruv |
| 3 | **Leaflet statik import** — map kerak bo'lmasa ham yuklanadi | PostVacancyView, MapView | Bundle hajmi ortishi |
| 4 | **Virtual scrolling yo'q** — uzun ro'yxatlar to'liq renderlanadi | SearchView, HomeView | Katta datada DOM sekinlashishi |
| 5 | **Load More** — yangi sahifa eskisini almashtiradi | SearchView/vacancy store | Pagination buzilgan |
| 6 | **Leaked event listeners** — `visibilitychange` tozalanmaydi | VacancyDetailView, MyVacanciesView | Memory leak |
| 7 | **`useApi` composable** ishlatilmaydi | composables/useApi.js | Dead code |

## 5.3 Database Performance

| # | Muammo | Ta'sir |
|---|---|---|
| 1 | UUID primary keys everywhere | 4.5x kattaroq indekslar, fragmentatsiya |
| 2 | `vacancies.category` string, FK emas | JOIN optimizatsiyasi yo'q |
| 3 | `vacancies.city` string, FK emas | Referential integrity yo'q |
| 4 | `vacancies.employer_id` indeks yo'q | "Mening vakansiyalarim" sekin |
| 5 | `vacancies.expires_at` indeks yo'q | Muddati tugagan vakansiyalarni qidirish sekin |
| 6 | `subscriptions.expires_at` indeks yo'q | Obuna tekshiruvi sekin |
| 7 | `payments.external_id` unique emas | Double-payment xavfi |
| 8 | `messages.is_read` per-message | Unread hisoblash sekin |
| 9 | CACHE_STORE=database default | Har bir cache so'rov DB'ga boradi |

---

# VI. NUTGRAM CRITICAL BUG

**Fayl:** `config/nutgram.php` — `'cache' => 'array'`

`array` cache driver xotirada saqlanadi va **requestlar orasida saqlanmaydi**. Webhook rejimida conversation state (RegistrationConversation, PostVacancyConversation va boshqalar) **har request'da yo'qoladi**. Bu butun bot conversationlarini buzadi.

**Yechim:** `'cache' => 'redis'` yoki `'cache' => 'database'`

---

# VII. I18N MUAMMOLARI

Frontend'da ko'p joyda o'zbek matni hardcoded:
- `VacancyCard.vue` — `"gacha"` (ruscha tarjima yo'q)
- `MyVacanciesView.vue` — `"Bepul"`, `"Faollashtirish"`, `"min oldin"`, `"soat oldin"`
- `HomeView.vue` — `"Xayrli tong"`, `"Xayrli kun"`, `"Xayrli kech"`
- `MapView.vue` — `"Geolokatsiyani yoqing..."`
- `ApplicationsView.vue` — `"gacha"`

Rus tilidagi foydalanuvchilar uchun UX buzilgan.

---

# VIII. OPTIMIZATSIYA REJASI

## Faza 1: CRITICAL — Xavfsizlik (1-2 kun)

### 1.1 Authorization tizimini joriy qilish
```
Yaratish kerak:
├── app/Policies/VacancyPolicy.php
├── app/Policies/ApplicationPolicy.php
├── app/Policies/ChatPolicy.php
└── app/Policies/ReviewPolicy.php

VacancyController::update() va destroy() — $this->authorize() qo'shish
ApplicationController::updateStage() va withdraw() — $this->authorize()
```

### 1.2 Click webhook imzo tekshiruvini qo'shish
```
ClickController.php — sign_string va sign_time tekshiruvi
```

### 1.3 Admin parolni env'ga ko'chirish
```
AdminSeeder: 'password' => Hash::make(env('ADMIN_PASSWORD', Str::random(32)))
```

### 1.4 Sanctum token expiration sozlash
```
config/sanctum.php: 'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 43200) // 30 kun
```

### 1.5 Nutgram cache driver'ni to'g'rilash
```
config/nutgram.php: 'cache' => env('NUTGRAM_CACHE', 'redis')
```

---

## Faza 2: HIGH — Arxitektura refactoring (3-5 kun)

### 2.1 Form Request klasslar yaratish
```
Yaratish kerak:
├── app/Http/Requests/StoreVacancyRequest.php
├── app/Http/Requests/UpdateVacancyRequest.php
├── app/Http/Requests/StoreApplicationRequest.php
├── app/Http/Requests/UpdateProfileRequest.php
├── app/Http/Requests/SendMessageRequest.php
└── ... (har bir endpoint uchun)
```

### 2.2 API Resource klasslar yaratish
```
Yaratish kerak:
├── app/Http/Resources/VacancyResource.php
├── app/Http/Resources/UserResource.php
├── app/Http/Resources/ApplicationResource.php
├── app/Http/Resources/VacancyCollection.php
└── ...
```

### 2.3 Employer middleware yaratish (DRY fix)
```
Yaratish kerak:
├── app/Http/Middleware/EnsureEmployerProfile.php
  → $request->user()->employerProfile tekshiruvini markazlashtirish
  → 15 joyda takrorlangan kod o'rniga middleware route'ga qo'yiladi
```

### 2.4 Database tranzaktsiyalar qo'shish
```
O'zgartirish kerak:
├── ApplicationController::store() → DB::transaction()
├── ReviewController::store() → DB::transaction()
├── ChatController::send() → DB::transaction()
├── PaymentService::complete() → DB::transaction()
```

---

## Faza 3: HIGH — Frontend DRY va Performance (3-5 kun)

### 3.1 Utility funksiyalarni markazlashtirish
```
Yaratish kerak:
├── resources/miniapp/src/utils/formatters.js
│   ├── formatSalary(min, max, t)
│   ├── formatNumber(num)
│   ├── formatDate(date, locale)
│   ├── timeAgo(date, t)
│   └── getInitial(name)
```

### 3.2 Statik ma'lumotlarni cache qilish (categories/cities store)
```
Yaratish kerak:
├── resources/miniapp/src/stores/reference.js
│   ├── categories (bir marta yuklanadi)
│   ├── cities (bir marta yuklanadi)
│   └── workTypes
```

### 3.3 `<keep-alive>` qo'shish
```
App.vue:
<router-view v-slot="{ Component }">
  <keep-alive :include="['HomeView', 'SearchView', 'ProfileView']">
    <component :is="Component" />
  </keep-alive>
</router-view>
```

### 3.4 Vacancy store pagination fix
```
vacancy.js:
fetchVacancies(params, append = false) {
  if (append) {
    vacancies.value = [...vacancies.value, ...response.data.data]
  } else {
    vacancies.value = response.data.data
  }
}
```

### 3.5 Leaflet lazy loading
```
PostVacancyView.vue:
const L = ref(null)
onMounted(async () => {
  const leaflet = await import('leaflet')
  L.value = leaflet.default
})
```

### 3.6 Hardcoded matnlarni i18n'ga ko'chirish
```
O'zgartirish kerak:
├── VacancyCard.vue — "gacha" → t('common.to')
├── MyVacanciesView.vue — barcha hardcoded matnlar
├── HomeView.vue — salomlashish matnlari
├── MapView.vue — geolocation matnlari
```

---

## Faza 4: MEDIUM — Database optimizatsiya (2-3 kun)

### 4.1 Indekslar qo'shish (migration)
```sql
-- vacancies jadvaliga
ADD INDEX idx_employer_id (employer_id)
ADD INDEX idx_expires_at (expires_at)
ADD INDEX idx_is_top (is_top)
ADD INDEX idx_is_urgent (is_urgent)

-- subscriptions jadvaliga
ADD INDEX idx_expires_at (expires_at)

-- users jadvaliga
ADD INDEX idx_is_blocked (is_blocked)

-- payments jadvaliga
ADD UNIQUE idx_external_id (external_id) -- double payment oldini olish
```

### 4.2 Category/City FK'larni to'g'rilash (migration)
```
Yangi migration:
1. vacancies.category_id (UUID FK → categories.id) qo'shish
2. vacancies.city_id (UUID FK → cities.id) qo'shish
3. worker_profiles.city_id qo'shish
4. Mavjud string ma'lumotlarni migrate qilish
5. Eski string ustunlarni olib tashlash
```

### 4.3 Balance check constraint
```sql
ALTER TABLE users ADD CHECK (balance >= 0)
```

---

## Faza 5: MEDIUM — Backend Performance (2-3 kun)

### 5.1 N+1 so'rovlarni hal qilish
```
Recruiter/VacancyController:
→ countRecommendedCandidates() ni eager loading yoki subquery bilan almashtirish

ChatController:
→ unread_count ni withCount() yoki subquery bilan olish

ReviewController:
→ selectRaw('AVG(rating) as avg, COUNT(*) as cnt') — bitta so'rov
```

### 5.2 activeSubscription() cache qilish
```
User model:
public function activeSubscription() {
    return once(fn() => $this->subscriptions()->where('status', 'active')
        ->where('expires_at', '>', now())->first());
}
```

### 5.3 Matching algoritmini optimizatsiya qilish
```
MatchingService:
→ PHP'da hisoblash o'rniga database-level scoring
→ Redis cache bilan matching natijalarni saqlash
→ Background job orqali pre-compute qilish
```

---

## Faza 6: LOW — Kod sifati va DX (2-3 kun)

### 6.1 Backend refactoring
```
1. TelegramAuthService yaratish — initData validation markazlashtirish
2. AbstractPaymentGateway yaratish — ClickService, PaymeService, UzumService uchun
3. HasEmployerProfile trait yaratish
4. GeoService::haversine() ni yagona joy qilish
```

### 6.2 Frontend refactoring
```
1. VacancyDetailView.vue (844 qator) → decompose qilish:
   ├── VacancySalaryCard.vue
   ├── VacancyInfoGrid.vue
   ├── CompanyCard.vue
   └── VacancyActions.vue

2. Icon component yaratish (inline SVG o'rniga)

3. useApi composable'ni ishlatish yoki o'chirish

4. Global error handler qo'shish:
   app.config.errorHandler = (err) => { ... }

5. Loading state'larni ajratish:
   vacancy store: fetchLoading, applyLoading, createLoading
```

### 6.3 Testing infrastructure
```
1. database/factories/ yaratish (UserFactory, VacancyFactory, ...)
2. Feature testlar yozish (Auth, Vacancy CRUD, Payment webhook)
3. Policylar uchun unit testlar
```

---

## Faza 7: LOW — DevOps va Config (1 kun)

### 7.1 Production config
```
.env.production:
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
NUTGRAM_CACHE=redis
```

### 7.2 Dependency cleanup
```
composer.json:
- smalot/pdfparser: "*" → "^2.0"
- doctrine/dbal qo'shish (migration rollback uchun)
- pusher/pusher-php-server — kerak bo'lmasa olib tashlash

package.json:
- @telegram-apps/sdk — ishlatilmasa olib tashlash
```

---

# IX. USTUVORLIK MATRITSASI

| Faza | Prioritet | Vaqt | Ta'sir |
|------|-----------|------|--------|
| 1. Xavfsizlik | 🔴 CRITICAL | 1-2 kun | Hujumlardan himoya |
| 2. Arxitektura | 🟠 HIGH | 3-5 kun | Kod sifati, maintainability |
| 3. Frontend DRY/Perf | 🟠 HIGH | 3-5 kun | UX, i18n, performance |
| 4. Database | 🟡 MEDIUM | 2-3 kun | Query performance |
| 5. Backend Performance | 🟡 MEDIUM | 2-3 kun | API tezligi |
| 6. Kod sifati | 🟢 LOW | 2-3 kun | Developer experience |
| 7. DevOps | 🟢 LOW | 1 kun | Production readiness |

**Jami taxminiy vaqt: 14-22 ish kuni**

---

# X. UMUMIY STATISTIKA

| Ko'rsatkich | Qiymat |
|---|---|
| Xavfsizlik muammolari | 8 ta (6 critical) |
| DRY buzilishlari (backend) | 10 ta (~50+ takrorlanish) |
| DRY buzilishlari (frontend) | 10 ta (~30+ takrorlanish) |
| SOLID buzilishlari | 7 ta |
| Performance muammolari (backend) | 6 ta |
| Performance muammolari (frontend) | 7 ta |
| Database muammolari | 9 ta |
| Arxitektura muammolari | 10 ta |
| **Jami aniqlangan muammolar** | **~67 ta** |
