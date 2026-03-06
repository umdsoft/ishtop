# IshTop Telegram Bot — To'liq Texnik Topshiriq (TZ)

## 1. Umumiy ma'lumot

**Loyiha:** IshTop — Telegram orqali ishga joylashish platformasi
**Bot username:** @ishtop_bot
**Texnologiyalar:** Laravel 11.48 + nutgram/nutgram v4.0 + Vue 3 (Mini App)
**Til:** O'zbek (asosiy) + Rus

---

## 2. Foydalanuvchi turlari

### 2.1. Ish izlovchi (Worker)
- Telegram orqali rezume yaratadi
- Vakansiyalarni qidiradi va ariza beradi
- Savolnomalarni to'ldiradi
- Arizalar holatini kuzatadi
- Mini App orqali batafsil boshqaradi

### 2.2. Oddiy ish beruvchi (Casual Employer)
- Telegram orqali 1 martalik vakansiya e'lon beradi (pullik)
- Kelib tushgan arizalarni bot orqali ko'radi
- Savolnoma qo'shishi mumkin (oddiy)

### 2.3. Rekruter kompaniya (Company Recruiter)
- Web panel (Dashboard) orqali boshqaradi
- Ko'p vakansiyalar, savolnomalar, analitika
- Nomzodlar tavsiya tizimi
- Subscription rejalari (FREE, BUSINESS, RECRUITER_PRO, AGENCY, CORPORATE)
- Bot orqali ham kirish imkoni (web panelga yo'naltirish)

---

## 3. Mavjud holat (Allaqachon qilingan)

### 3.1. Mavjud handlerlar
| Fayl | Vazifa | Holat |
|------|--------|-------|
| `StartHandler.php` | /start, ro'yxatdan o'tish | ✅ Ishlaydi |
| `MenuHandler.php` | Bosh menyu, callback routing | ✅ Ishlaydi |
| `SearchHandler.php` | Ish qidirish, kategoriya/shahar filter, ariza berish | ✅ Ishlaydi |
| `SettingsHandler.php` | Til, bildirishnoma, balans, referal, akkaunt o'chirish | ✅ Ishlaydi |
| `HelpHandler.php` | Yordam va buyruqlar ro'yxati | ✅ Ishlaydi |
| `AppsHandler.php` | Arizalar ro'yxati, tafsilotlar | ✅ Ishlaydi |
| `ResumeHandler.php` | Rezume ko'rish/yaratish | ✅ Ishlaydi |
| `PostHandler.php` | E'lon berish, employer vakansiyalari | ✅ Ishlaydi |

### 3.2. Mavjud conversationlar
| Fayl | Vazifa | Holat |
|------|--------|-------|
| `RegistrationConversation.php` | 4 bosqich: til → telefon → OTP → ism | ✅ Ishlaydi |
| `ResumeBuilderConversation.php` | 12 bosqich: to'liq rezume yaratish | ✅ Ishlaydi |
| `PostVacancyConversation.php` | 8 bosqich: vakansiya yaratish | ✅ Ishlaydi |
| `QuestionnaireConversation.php` | Dinamik savolnoma to'ldirish | ✅ Ishlaydi |

### 3.3. Mavjud modellar
- `User` — telegram_id, phone, language, balance, referral_code
- `WorkerProfile` — rezume ma'lumotlari, search_status
- `EmployerProfile` — kompaniya ma'lumotlari, verification_level
- `Vacancy` — ikki tilli (uz/ru), status, salary, work_type
- `Application` — stage pipeline, questionnaire scoring

---

## 4. Taklif qilinayotgan o'zgarishlar

### 4.1. StartHandler — Telegram ma'lumotlarini darhol saqlash

**Hozirgi muammo:** Foydalanuvchi /start berganda, agar u ro'yxatdan o'tmagan bo'lsa — faqat RegistrationConversation boshlanadi. Telegram'dan keladigan ma'lumotlar (first_name, last_name, username, language_code) saqlanmaydi.

**Taklif:** /start bosilgan zahoti, Telegram user ma'lumotlari DB ga yozilsin:

```php
// StartHandler.php — yangilangan logika
public function __invoke(Nutgram $bot): void
{
    $tgUser = $bot->user();

    // 1-qadam: Telegram ma'lumotlarini darhol saqlash/yangilash
    $user = User::updateOrCreate(
        ['telegram_id' => $tgUser->id],
        [
            'first_name'     => $tgUser->first_name,
            'last_name'      => $tgUser->last_name,
            'username'        => $tgUser->username,
            'language'        => in_array($tgUser->language_code, ['uz', 'ru'])
                                    ? $tgUser->language_code : 'uz',
            'last_active_at'  => now(),
        ]
    );

    // 2-qadam: Agar is_verified bo'lsa — Bosh menyu
    if ($user->is_verified) {
        $this->showWelcome($bot, $user);
        return;
    }

    // 3-qadam: Ro'yxatdan o'tish (telefon tasdiqlash)
    $conversation = new RegistrationConversation();
    $conversation->referralCode = $this->extractReferralCode($bot);
    $conversation->begin($bot);
}
```

**Saqlanadiganlar:**
| Telegram field | DB field | Izoh |
|---|---|---|
| `user.id` | `telegram_id` | Unique identifier |
| `user.first_name` | `first_name` | Ism |
| `user.last_name` | `last_name` | Familiya (nullable) |
| `user.username` | `username` | @username (nullable) |
| `user.language_code` | `language` | uz/ru (default: uz) |
| `user.is_premium` | — | Keyinchalik qo'shish mumkin |
| — | `last_active_at` | Har start'da yangilanadi |

### 4.2. RegistrationConversation — soddalshtirish

**Hozirgi flow:** Til → Telefon → OTP → Ism
**Yangi flow:** Telefon → OTP → Tayyor!

Sabab: /start'da user allaqachon yaratilgan, ism va til Telegram'dan olingan. Faqat telefon tasdiqlash qoladi.

```
/start
  ↓
User DB'ga yozildi (telegram data)
  ↓
Telefon raqam so'rash (ReplyKeyboard: "📱 Raqamni yuborish")
  ↓
OTP yuborish va tekshirish
  ↓
is_verified = true
  ↓
Bosh menyu ko'rsatish
```

### 4.3. Bosh menyu — yangilangan tuzilish

**Hozirgi menyu:**
```
[🔍 Ish qidirish] [📝 Rezume]
[📢 E'lon berish] [📋 Arizalarim]
[🌐 Mini App]
[⚙️ Sozlamalar] [❓ Yordam]
```

**Yangi menyu:**
```
[🔍 Ish qidirish] [📝 Rezume]
[📢 E'lon berish] [📋 Arizalarim]
[🌐 Mini App ochish]
[💼 Ish beruvchi paneli]
[⚙️ Sozlamalar] [❓ Yordam]
```

O'zgarishlar:
- "Mini App" tugmasi — WebApp URL bilan (mini_app button emas, url button)
- "Ish beruvchi paneli" — employer profil bor bo'lsa ko'rsatilsin, web panelga yo'naltiradi
- Ikki tilli barcha matnlar (user.language asosida)

---

## 5. Bot buyruqlari ro'yxati

| Buyruq | Vazifa | Handler |
|--------|--------|---------|
| `/start` | Botni ishga tushirish, ro'yxatdan o'tish | StartHandler |
| `/menu` | Bosh menyu | MenuHandler |
| `/search` | Ish qidirish | SearchHandler |
| `/resume` | Rezume yaratish/tahrirlash | ResumeBuilderConversation |
| `/post` | Vakansiya e'lon berish | PostVacancyConversation |
| `/apps` | Mening arizalarim | AppsHandler |
| `/settings` | Sozlamalar | SettingsHandler |
| `/help` | Yordam | HelpHandler |
| `/cancel` | Joriy amalni bekor qilish | Fallback |
| `/web` | Web panelga yo'naltirish | MenuHandler::web |

---

## 6. Foydalanuvchi flowlari (batafsil)

### 6.1. Yangi foydalanuvchi flow

```
Foydalanuvchi /start bosadi
    ↓
Bot: User ma'lumotlarini DB ga yozadi (telegram_id, first_name, last_name, username, language)
    ↓
Bot: "Salom, [Ism]! IshTop ga xush kelibsiz!"
Bot: "📱 Telefon raqamingizni tasdiqlang:"
    ↓
[📱 Raqamni yuborish] — ReplyKeyboard tugma
    ↓
Foydalanuvchi: Kontakt yuboradi yoki +998XXXXXXXXX yozadi
    ↓
Bot: SMS orqali 6 xonali OTP yuboradi
Bot: "📩 [raqam]ga kod yuborildi. 6 xonali kodni kiriting:"
    ↓
Foydalanuvchi: OTP ni kiritadi
    ↓
Bot: is_verified = true, phone = [raqam]
Bot: "✅ Ro'yxatdan o'tdingiz! 🎉"
Bot: Bosh menyu ko'rsatadi (InlineKeyboard)
```

### 6.2. Rezume yaratish flow

```
/resume yoki menu:resume
    ↓
[Agar rezume bor] → Ko'rsatadi + Tahrirlash/O'chirish tugmalari
[Agar rezume yo'q] → ResumeBuilderConversation boshlaydi
    ↓
1. To'liq ism (default: User.first_name + last_name)
2. Tug'ilgan sana (DD.MM.YYYY)
3. Jins (Erkak/Ayol — InlineKeyboard)
4. Shahar (City modeldan — InlineKeyboard)
5. Tuman/Mahalla (matn yoki "-" skip)
6. Ta'lim darajasi (o'rta/o'rta-maxsus/bakalavr/magistr)
7. Mutaxassislik (matn)
8. Tajriba (0/1-2/3-5/5+ — InlineKeyboard)
9. Ko'nikmalar (vergul bilan ajratilgan)
10. Ish turi (multi-select: to'liq/yarim/masofaviy/vaqtinchalik)
11. Kutilayotgan maosh (MIN-MAX yoki "-")
12. Bio/Qo'shimcha (matn yoki skip)
    ↓
WorkerProfile yaratiladi
search_status = OPEN
    ↓
Rezume ko'rsatiladi (formatlangan)
[✏️ Tahrirlash] [◀️ Menyu]
```

### 6.3. Ish qidirish flow

```
/search yoki menu:search
    ↓
Bot: "🔍 Ish qidirish"
Bot: Faol vakansiyalar soni
[📂 Kategoriya] [📍 Shahar]
[📋 Barcha vakansiyalar]
[🌐 Mini App da qidirish]
[◀️ Orqaga]
    ↓
[Kategoriya tanlasa] → searchByCategory → showVacancies(filter=category)
[Shahar tanlasa] → searchByCity → showVacancies(filter=city)
[Barchasi] → showVacancies(filter=all)
    ↓
Vakansiyalar ro'yxati (5 ta sahifada):
  🔥 1. Senior PHP Developer
  📍 Toshkent | 💰 5,000,000 - 8,000,000 so'm
  🏢 TechCorp

  [👁 Senior PHP Developer]
  [◀️ 1/5 ▶️]
  [◀️ Orqaga]
    ↓
[Vakansiyani tanlasa] → showVacancyDetail
  📌 *Senior PHP Developer*
  🏢 TechCorp
  📂 IT
  📍 Toshkent
  💰 5M - 8M so'm
  🏢 To'liq stavka
  ⏱ 3-5 yil tajriba

  📝 Tavsif: ...
  📋 Talablar: ...
  ✅ Vazifalar: ...

  👁 156 | 📝 23

  [📝 Ariza berish]
  [◀️ Orqaga]
    ↓
[Ariza berish] →
  - Rezume bormi? → Yo'q → "Avval rezume yarating" + [📝 Rezume yaratish]
  - Allaqachon ariza berganmi? → "Allaqachon ariza bergansiz"
  - OK → Application yaratadi (stage=new, source=telegram)
  - Vacancy.applications_count++
  - "✅ Ariza yuborildi!"
```

### 6.4. E'lon berish flow (oddiy foydalanuvchi uchun)

```
/post yoki menu:post
    ↓
Bot: "📢 E'lon berish"
Bot: "💰 Narx: 35,000 so'm (15 kun)"
[📝 E'lon yaratish]
[◀️ Orqaga]
    ↓
PostVacancyConversation boshlaydi:
    ↓
[Agar EmployerProfile yo'q] →
  Bot: "Avval kompaniya nomini kiriting:"
  User: "Mening do'konim"
  → EmployerProfile yaratiladi (company_name, user_id)
    ↓
1. Vakansiya sarlavhasi (min 3 belgi)
2. Kategoriya (Category modeldan — InlineKeyboard)
3. Shahar (City modeldan — InlineKeyboard)
4. Tavsif (min 10 belgi)
5. Talablar (matn yoki "-" skip)
6. Maosh turi (Aniq/Diapazon/Kelishiladi — InlineKeyboard)
7. Maosh summasi (tur bo'yicha)
8. Ish turi (To'liq/Yarim/Masofaviy/Vaqtinchalik)
9. Aloqa usuli (Telegram/Telefon/Ikkalasi)
    ↓
Vakansiya preview ko'rsatiladi:
  📌 *Sotuvchi kerak*
  📂 Savdo
  📍 Toshkent
  💰 3,000,000 - 4,000,000 so'm
  🏢 To'liq stavka
  📝 Tavsif: ...

  [✅ Tasdiqlash]
  [✏️ Tahrirlash]
  [❌ Bekor qilish]
    ↓
[Tasdiqlash] →
  Vacancy yaratiladi (status=PENDING)
  Bot: "✅ E'loningiz moderatsiyaga yuborildi!"
  Bot: "Admin tasdiqlangandan so'ng 15 kun davomida faol bo'ladi."
    ↓
[Admin tasdiqlaydi] →
  status = ACTIVE
  published_at = now()
  expires_at = now() + 15 kun
  Bot: User ga xabar yuboriladi: "✅ E'loningiz tasdiqlandi va faol!"
```

### 6.5. Arizalar boshqaruvi flow

```
/apps yoki menu:apps
    ↓
[Rezume yo'q] → "Avval rezume yarating" + /resume
[Ariza yo'q] → "Hali ariza yo'q" + [🔍 Ish qidirish]
[Arizalar bor] →
    ↓
📝 *Mening arizalarim* (12)

🆕 *Yangi* (5)
👀 *Ko'rib chiqilmoqda* (3)
⭐ *Tanlab olingan* (2)
🎯 *Intervyu* (1)
❌ *Rad etilgan* (1)

[🆕 Sotuvchi kerak]
[👀 Dasturchi]
[⭐ Dizayner]
...
[🌐 Mini App ochish]
[◀️ Orqaga]
    ↓
[Arizani tanlasa] → showApplicationDetails
  🆕 *Sotuvchi kerak*
  🏢 Do'kon LLC
  📍 Toshkent
  💰 3M - 4M so'm
  📊 Holat: Yangi
  📅 Yuborildi: 05.03.2026 14:30

  ⚠️ Savolnoma to'ldirilmagan

  [📋 Savolnomani to'ldirish]
  [🚫 Arizani bekor qilish]
  [◀️ Orqaga]
```

### 6.6. Sozlamalar flow

```
/settings yoki menu:settings
    ↓
⚙️ *Sozlamalar*

👤 Ism: Ali Valiyev
📞 Telefon: +998901234567
🌐 Til: 🇺🇿 O'zbekcha
🔔 Bildirishnomalar: 🔔 Yoqilgan
💰 Balans: 50,000 so'm

[🌐 Tilni o'zgartirish]
[🔔 Bildirishnomalarni o'chirish]
[💰 Balansni to'ldirish]
[🎁 Referal dastur]
[🗑 Akkauntni o'chirish]
[◀️ Orqaga]
```

---

## 7. Mini App arxitekturasi

### 7.1. Mini App nima?
Telegram WebApp — bot ichida ochiluvchi to'liq web ilovasi. `initData` orqali autentifikatsiya qilinadi.

### 7.2. Mini App sahifalari

| Path | Vazifa |
|------|--------|
| `/` | Bosh sahifa — vakansiyalar ro'yxati |
| `/vacancies` | Vakansiyalar qidirish (filter, xarita) |
| `/vacancy/:id` | Vakansiya tafsilotlari |
| `/profile` | Foydalanuvchi profili va rezume |
| `/applications` | Mening arizalarim |
| `/application/:id` | Ariza tafsilotlari |
| `/post` | Vakansiya e'lon berish |
| `/settings` | Sozlamalar |

### 7.3. Mini App autentifikatsiya

```
Telegram Bot → Mini App ochiladi → initData yuboriladi
    ↓
Frontend: POST /api/auth/telegram
Body: { initData: "..." }
    ↓
Backend: TelegramWebAppAuth middleware
  1. HMAC-SHA256 bilan initData tekshiriladi
  2. telegram_id bo'yicha User topiladi
  3. Sanctum token qaytariladi
    ↓
Frontend: Token saqlanadi, keyingi so'rovlarda Authorization: Bearer <token>
```

### 7.4. Mini App texnologiyalari
- Vue 3 + Pinia + Vue Router
- Tailwind CSS
- Telegram WebApp SDK (`@twa-dev/sdk`)
- Axios (API calls)
- Alohida entry point: `resources/js/miniapp/app.js`
- Vite build: alohida chunk

### 7.5. Mini App va Bot o'rtasidagi aloqa

Bot → Mini App:
```javascript
// Bot'dan Mini App'ga ma'lumot yuborish
InlineKeyboardButton::make('🌐 Mini App', web_app: WebAppInfo::make(url: 'https://ishtop.uz/miniapp'))
```

Mini App → Bot:
```javascript
// Mini App'dan Bot'ga ma'lumot qaytarish
Telegram.WebApp.sendData(JSON.stringify({ action: 'applied', vacancy_id: '...' }))
```

---

## 8. Bot + Mini App dual mode ishlashi

### 8.1. Qanday ishlaydi?

**Oddiy bot rejimi (Regular mode):**
- Foydalanuvchi inline tugmalar orqali navigatsiya qiladi
- Matnli javoblar, callback query'lar
- Barcha asosiy funksiyalar ishlaydi
- Cheklangan UI (faqat matn + tugmalar)

**Mini App rejimi:**
- Boy UI (kartochkalar, filtrlar, xarita, slidebar)
- Xuddi mobil ilovadek tajriba
- Tezkor va qulay
- Telegram WebApp SDK integratsiyasi

### 8.2. Qachon qaysi rejimdan foydalaniladi?

| Funksiya | Oddiy bot | Mini App |
|----------|-----------|----------|
| Ro'yxatdan o'tish | ✅ (asosiy) | ❌ |
| Ish qidirish (oddiy) | ✅ | ✅ (boy UI) |
| Xarita orqali qidirish | ❌ | ✅ |
| Rezume yaratish | ✅ | ✅ |
| Ariza berish | ✅ | ✅ |
| Savolnoma to'ldirish | ✅ | ✅ (qulay) |
| Arizalar ro'yxati | ✅ (oddiy) | ✅ (batafsil) |
| E'lon berish | ✅ | ✅ |
| Sozlamalar | ✅ | ✅ |
| Balans to'ldirish | ✅ (link) | ✅ (to'liq) |

---

## 9. Database sxemasi (mavjud + o'zgarishlar)

### 9.1. users jadvali (mavjud — o'zgartirish shart emas)
```sql
- id (UUID, PK)
- telegram_id (bigInt, unique) ← /start da saqlanadi
- phone (nullable) ← OTP tasdiqlanganda
- email (nullable)
- first_name ← Telegram'dan
- last_name (nullable) ← Telegram'dan
- username (nullable) ← Telegram'dan
- language (enum: uz/ru) ← Telegram language_code'dan
- avatar_url (nullable)
- is_verified (boolean, default: false) ← OTP tasdiqlanganda true
- is_blocked (boolean, default: false)
- last_active_at (timestamp) ← Har /start da yangilanadi
- referral_code (unique, 8 char)
- referred_by (FK → users.id)
- balance (decimal 12,2)
- active_employer_id (FK → employer_profiles.id)
- password (nullable) ← Web panel uchun
- notifications_enabled (boolean, default: true)
- created_at, updated_at, deleted_at
```

### 9.2. worker_profiles jadvali (mavjud)
```sql
- id (UUID, PK)
- user_id (FK → users.id, unique)
- full_name, birth_date, gender
- city, district
- education_level, specialty
- experience_years (int)
- skills (JSON array)
- expected_salary_min, expected_salary_max
- work_types (JSON array)
- bio (text)
- photo_url, resume_file_url
- search_status (enum: open/passive/closed)
- latitude, longitude
- views_count
```

### 9.3. employer_profiles jadvali (mavjud)
```sql
- id (UUID, PK)
- user_id (FK → users.id)
- company_name
- industry, description
- address, phone, website
- logo_url, cover_url
- employees_count, stir_number
- verification_level (enum: new/confirmed/verified/top)
- rating, rating_count, response_time_avg
- latitude, longitude
```

### 9.4. vacancies jadvali (mavjud)
```sql
- id (UUID, PK)
- employer_id (FK → employer_profiles.id)
- language (uz/ru)
- title_uz, title_ru
- category, city, district
- description_uz, description_ru
- requirements_uz, requirements_ru
- responsibilities_uz, responsibilities_ru
- salary_min, salary_max, salary_type
- work_type, experience_required
- contact_phone, contact_method
- views_count, applications_count
- status (draft/pending/active/paused/closed/expired)
- is_top, is_urgent
- has_questionnaire
- published_at, expires_at
```

### 9.5. applications jadvali (mavjud)
```sql
- id (UUID, PK)
- vacancy_id + worker_id (unique constraint)
- stage (new/reviewed/shortlisted/interview/offered/hired/rejected)
- cover_letter
- questionnaire_score, questionnaire_max_score
- questionnaire_answers (JSON)
- questionnaire_completed, questionnaire_completed_at
- knockout_passed
- recruiter_rating, matching_score
- source (telegram/web/api)
- viewed_at, shortlisted_at, interviewed_at, offered_at
- rejected_reason
```

---

## 10. API endpointlar

### 10.1. Public API (Bot + Mini App uchun)

```
POST   /api/auth/telegram          — initData bilan autentifikatsiya
POST   /api/auth/send-otp          — OTP yuborish
POST   /api/auth/verify-otp        — OTP tekshirish

GET    /api/vacancies              — Vakansiyalar ro'yxati (filter, pagination)
GET    /api/vacancies/{id}         — Vakansiya tafsilotlari
GET    /api/categories             — Kategoriyalar ro'yxati
GET    /api/cities                 — Shaharlar ro'yxati

GET    /api/profile                — Foydalanuvchi profili
PUT    /api/profile                — Profil yangilash
GET    /api/profile/resume         — Rezume
PUT    /api/profile/resume         — Rezume yangilash
POST   /api/profile/resume         — Rezume yaratish

GET    /api/applications           — Mening arizalarim
POST   /api/applications           — Ariza berish
GET    /api/applications/{id}      — Ariza tafsilotlari
DELETE /api/applications/{id}      — Arizani bekor qilish

POST   /api/vacancies              — Vakansiya e'lon berish (oddiy user)

GET    /api/search                 — Qidirish (keyword, filters)
```

### 10.2. Recruiter API (Web panel uchun — mavjud)
```
GET    /api/recruiter/vacancies         — Mening vakansiyalarim
POST   /api/recruiter/vacancies         — Vakansiya yaratish
GET    /api/recruiter/vacancies/{id}    — Vakansiya tafsilotlari
PUT    /api/recruiter/vacancies/{id}    — Vakansiya tahrirlash
...
GET    /api/recruiter/candidates/recommended  — Tavsiya etilgan nomzodlar
...
```

---

## 11. Bildirishnomalar tizimi

### 11.1. Foydalanuvchiga (ish izlovchi)

| Hodisa | Xabar |
|--------|-------|
| Ariza ko'rib chiqildi | "👀 Sizning arizangiz '[vacancy]' vakansiyasi uchun ko'rib chiqilmoqda" |
| Ariza tanlab olindi | "⭐ Tabriklaymiz! '[vacancy]' uchun tanlov ro'yxatiga tushdingiz" |
| Intervyuga chaqirildi | "🎯 '[vacancy]' uchun intervyuga chaqirildingiz" |
| Taklif keldi | "🎉 '[vacancy]' vakansiyasi uchun ish taklifi keldi!" |
| Ariza rad etildi | "❌ Afsuski, '[vacancy]' uchun arizangiz rad etildi" |
| Mos vakansiya topildi | "🔔 Sizga mos yangi vakansiya: '[vacancy]' — [city], [salary]" |

### 11.2. Ish beruvchiga

| Hodisa | Xabar |
|--------|-------|
| Yangi ariza keldi | "📝 '[vacancy]' uchun yangi ariza: [worker_name]" |
| E'lon tasdiqlandi | "✅ '[vacancy]' e'loningiz tasdiqlandi va faol!" |
| E'lon rad etildi | "❌ '[vacancy]' e'loningiz rad etildi. Sabab: [reason]" |
| E'lon muddati tugayapti | "⚠️ '[vacancy]' e'loningiz 2 kundan so'ng tugaydi" |

### 11.3. Bildirishnoma yuborish usuli
```php
// TelegramNotificationService.php
class TelegramNotificationService
{
    public function notify(User $user, string $text, ?InlineKeyboardMarkup $keyboard = null): void
    {
        if (!$user->notifications_enabled || !$user->telegram_id) {
            return;
        }

        $bot = app(Nutgram::class);
        $bot->sendMessage(
            chat_id: $user->telegram_id,
            text: $text,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $keyboard,
        );
    }
}
```

---

## 12. Webhook va Polling

### 12.1. Development (hozir)
```bash
# Polling rejimi — local development uchun
php artisan nutgram:polling
```

### 12.2. Production
```bash
# Webhook o'rnatish
php artisan nutgram:hook:set https://ishtop.uz/telegram/webhook

# Webhook o'chirish
php artisan nutgram:hook:remove
```

**Webhook route:**
```php
// routes/web.php
Route::post('/telegram/webhook', function () {
    app(Nutgram::class)->run();
});
```

---

## 13. Xavfsizlik

### 13.1. Autentifikatsiya
- **Bot:** `telegram_id` orqali User topiladi
- **Mini App:** `initData` HMAC-SHA256 bilan tekshiriladi
- **Web Panel:** Sanctum token + session

### 13.2. Ruxsatlar
- Foydalanuvchi faqat o'z arizalarini ko'radi
- Ish beruvchi faqat o'z vakansiyalariga ariza ko'radi
- Admin barcha ma'lumotlarni ko'radi (Filament panel)

### 13.3. Rate limiting
- OTP: 3 ta urinish / 5 daqiqa
- Ariza berish: 10 ta / soat
- Vakansiya yaratish: 3 ta / kun (oddiy foydalanuvchi)
- API: 60 so'rov / daqiqa

### 13.4. Ma'lumotlar ximoyasi
- Phone raqam faqat tasdiqlangan foydalanuvchilarda saqlanadi
- Soft delete — haqiqiy o'chirish emas
- Referral code — random 8-char hex

---

## 14. To'lov tizimi

### 14.1. Oddiy foydalanuvchi
- 1 ta vakansiya e'lon = 35,000 so'm (15 kun)
- Balansdan yechiladi
- Balans to'ldirish: Click, Payme, Uzum (integratsiya kerak)

### 14.2. TOP/Urgent e'lon
- TOP ko'rsatish = +25,000 so'm (7 kun)
- Urgent belgi = +15,000 so'm (3 kun)

### 14.3. Rekruter kompaniya
- Subscription rejalari (oylik):
  - FREE: 1 vakansiya, cheklangan
  - BUSINESS: 99,000 so'm, 10 vakansiya
  - RECRUITER_PRO: 499,000 so'm, cheksiz
  - AGENCY: 999,000 so'm, ko'p employer
  - CORPORATE: 2,490,000 so'm, to'liq

---

## 15. Admin panel (Filament)

### 15.1. Mavjud funksiyalar
- Foydalanuvchilar boshqaruvi
- Vakansiyalar moderatsiyasi (tasdiqlash/rad etish)
- Kategoriyalar va shaharlar boshqarish
- Arizalar ko'rish
- Obunalar boshqarish
- To'lovlar tarixi

### 15.2. Kerak bo'ladigan qo'shimchalar
- Vakansiya moderatsiya queue (PENDING statusdagilar)
- Foydalanuvchi bloklash/ban
- Statistika dashboard
- Bot statistikasi (faol foydalanuvchilar, ro'yxatdan o'tganlar)

---

## 16. Loyiha fayl tuzilishi

```
app/
  Telegram/
    Handlers/
      StartHandler.php        ← O'zgartirish kerak
      MenuHandler.php          ← O'zgartirish kerak (ikki tilli)
      SearchHandler.php        ✅ Tayyor
      SettingsHandler.php      ✅ Tayyor
      HelpHandler.php          ✅ Tayyor
      AppsHandler.php          ✅ Tayyor
      ResumeHandler.php        ✅ Tayyor
      PostHandler.php          ✅ Tayyor
    Conversations/
      RegistrationConversation.php  ← O'zgartirish kerak (soddalshtirish)
      ResumeBuilderConversation.php ✅ Tayyor
      PostVacancyConversation.php   ✅ Tayyor
      QuestionnaireConversation.php ✅ Tayyor
    Keyboards/
      MainMenuKeyboard.php     ← O'zgartirish kerak
    Services/
      TelegramNotificationService.php  ← Yangi
  Services/
    MatchingService.php        ✅ Tayyor
    SmsService.php             ✅ Tayyor
    ScoringService.php         ✅ Tayyor
    VacancyService.php         ✅ Tayyor
    SubscriptionLimitService.php ✅ Tayyor

resources/js/
  miniapp/                     ← Yangi (Mini App)
    app.js
    router/index.js
    stores/
    views/
    components/

routes/
  telegram.php                 ← O'zgartirish kerak (yangi callbacklar)
```

---

## 17. Amalga oshirish rejasi (bosqichlar)

### Bosqich 1: Bot asosini to'g'rilash
1. ✏️ `StartHandler` — Telegram data'ni darhol DB ga yozish
2. ✏️ `RegistrationConversation` — soddalshtirish (til olib tashlash, ism Telegram'dan)
3. ✏️ `MainMenuKeyboard` — ikki tilli qilish, yangi tugmalar
4. ✏️ `MenuHandler` — ikki tilli matnlar
5. ✏️ `routes/telegram.php` — yangi callbacklarni qo'shish

### Bosqich 2: Bildirishnomalar
6. 🆕 `TelegramNotificationService` — bot orqali xabar yuborish
7. Ariza holati o'zgarganda bildirishnoma
8. Yangi mos vakansiya topilganda bildirishnoma
9. E'lon moderatsiya natijasi

### Bosqich 3: Mini App
10. 🆕 Mini App entry point va routing
11. 🆕 Vakansiyalar sahifasi (boy UI, filtrlar)
12. 🆕 Rezume sahifasi
13. 🆕 Arizalar sahifasi
14. 🆕 Profil va sozlamalar

### Bosqich 4: Webhook va Production
15. Webhook o'rnatish
16. SSL sertifikat
17. Rate limiting
18. Error handling va logging

---

## 18. Tekshirish cheklisti

- [ ] /start bosilganda user ma'lumotlari DB ga yozilishi
- [ ] Yangi user uchun ro'yxatdan o'tish (telefon → OTP)
- [ ] Qayta /start bosilganda bosh menyu ko'rsatilishi
- [ ] Rezume yaratish (12 bosqich)
- [ ] Ish qidirish (kategoriya, shahar, barcha)
- [ ] Ariza berish
- [ ] E'lon berish (PostVacancyConversation)
- [ ] Arizalar ro'yxati va tafsilotlari
- [ ] Sozlamalar (til, bildirishnoma, balans, referal, o'chirish)
- [ ] Ikki tilli ishlashi (uz/ru)
- [ ] Mini App ochilishi va autentifikatsiya
- [ ] Bildirishnomalar ishlashi
- [ ] To'lov integratsiyasi
- [ ] Webhook production'da ishlashi
- [ ] Admin panelda moderatsiya

---

## 19. Xulosa

IshTop Telegram bot — bu foydalanuvchilar uchun ikkita rejimda ishlaydigan platforma:

1. **Oddiy bot** — tez va qulay, Telegram'dan chiqmasdan barcha asosiy funksiyalar
2. **Mini App** — boy UI, xarita, filtrlar, batafsil boshqaruv

Bot barcha uchun ochiq:
- **Ish izlovchi** → rezume yaratadi, vakansiya qidiradi, ariza beradi
- **Oddiy ish beruvchi** → 1 martalik e'lon beradi (pullik)
- **Rekruter kompaniya** → Web panel orqali ko'p vakansiyalarni boshqaradi

Mavjud kod bazasi 80% tayyor. Asosiy o'zgarishlar:
1. StartHandler'da Telegram data'ni darhol saqlash
2. RegistrationConversation'ni soddalshtirish
3. Bildirishnomalar tizimi
4. Mini App yaratish
