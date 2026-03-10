<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class SkillsSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            // =============================================
            // 1. IT (Parent)
            // =============================================
            'it' => [
                ['uz' => 'Dasturlash', 'ru' => 'Программирование'],
                ['uz' => 'Algoritmlar va ma\'lumotlar tuzilmasi', 'ru' => 'Алгоритмы и структуры данных'],
                ['uz' => 'Git versiyalarni boshqarish', 'ru' => 'Git управление версиями'],
                ['uz' => 'Ma\'lumotlar bazasi', 'ru' => 'Базы данных'],
                ['uz' => 'API integratsiya', 'ru' => 'API интеграция'],
                ['uz' => 'Ingliz tili (texnik)', 'ru' => 'Английский язык (технический)'],
                ['uz' => 'Jamoaviy ishla', 'ru' => 'Командная работа'],
                ['uz' => 'Muammolarni hal qilish', 'ru' => 'Решение проблем'],
            ],

            // IT - Frontend
            'it-frontend' => [
                ['uz' => 'HTML/CSS', 'ru' => 'HTML/CSS'],
                ['uz' => 'JavaScript', 'ru' => 'JavaScript'],
                ['uz' => 'TypeScript', 'ru' => 'TypeScript'],
                ['uz' => 'React', 'ru' => 'React'],
                ['uz' => 'Vue.js', 'ru' => 'Vue.js'],
                ['uz' => 'Angular', 'ru' => 'Angular'],
                ['uz' => 'Tailwind CSS', 'ru' => 'Tailwind CSS'],
                ['uz' => 'SASS/SCSS', 'ru' => 'SASS/SCSS'],
                ['uz' => 'Responsive dizayn', 'ru' => 'Адаптивный дизайн'],
                ['uz' => 'REST API bilan ishlash', 'ru' => 'Работа с REST API'],
                ['uz' => 'Webpack/Vite', 'ru' => 'Webpack/Vite'],
                ['uz' => 'Next.js/Nuxt.js', 'ru' => 'Next.js/Nuxt.js'],
            ],

            // IT - Backend
            'it-backend' => [
                ['uz' => 'PHP', 'ru' => 'PHP'],
                ['uz' => 'Laravel', 'ru' => 'Laravel'],
                ['uz' => 'Node.js', 'ru' => 'Node.js'],
                ['uz' => 'Python', 'ru' => 'Python'],
                ['uz' => 'Django/FastAPI', 'ru' => 'Django/FastAPI'],
                ['uz' => 'Java', 'ru' => 'Java'],
                ['uz' => 'Go', 'ru' => 'Go'],
                ['uz' => 'MySQL/PostgreSQL', 'ru' => 'MySQL/PostgreSQL'],
                ['uz' => 'Redis', 'ru' => 'Redis'],
                ['uz' => 'REST API yaratish', 'ru' => 'Разработка REST API'],
                ['uz' => 'Microservices arxitektura', 'ru' => 'Микросервисная архитектура'],
                ['uz' => 'Docker', 'ru' => 'Docker'],
            ],

            // IT - Mobile
            'it-mobile' => [
                ['uz' => 'Flutter', 'ru' => 'Flutter'],
                ['uz' => 'Dart', 'ru' => 'Dart'],
                ['uz' => 'React Native', 'ru' => 'React Native'],
                ['uz' => 'Swift (iOS)', 'ru' => 'Swift (iOS)'],
                ['uz' => 'Kotlin (Android)', 'ru' => 'Kotlin (Android)'],
                ['uz' => 'Java (Android)', 'ru' => 'Java (Android)'],
                ['uz' => 'Firebase', 'ru' => 'Firebase'],
                ['uz' => 'REST API integratsiya', 'ru' => 'Интеграция REST API'],
                ['uz' => 'Push-bildirishnomalar', 'ru' => 'Push-уведомления'],
                ['uz' => 'App Store/Google Play nashr qilish', 'ru' => 'Публикация в App Store/Google Play'],
                ['uz' => 'UI/UX mobil dizayn', 'ru' => 'UI/UX мобильный дизайн'],
                ['uz' => 'SQLite/Realm', 'ru' => 'SQLite/Realm'],
            ],

            // IT - Designer
            'it-designer' => [
                ['uz' => 'Figma', 'ru' => 'Figma'],
                ['uz' => 'Adobe Photoshop', 'ru' => 'Adobe Photoshop'],
                ['uz' => 'Adobe Illustrator', 'ru' => 'Adobe Illustrator'],
                ['uz' => 'UI dizayn', 'ru' => 'UI дизайн'],
                ['uz' => 'UX tadqiqot', 'ru' => 'UX исследования'],
                ['uz' => 'Prototiplash', 'ru' => 'Прототипирование'],
                ['uz' => 'Dizayn tizimlar', 'ru' => 'Дизайн системы'],
                ['uz' => 'Mobil ilova dizayni', 'ru' => 'Дизайн мобильных приложений'],
                ['uz' => 'Web dizayn', 'ru' => 'Веб дизайн'],
                ['uz' => 'Wireframing', 'ru' => 'Wireframing'],
                ['uz' => 'Foydalanuvchi tajribasini tahlil qilish', 'ru' => 'Анализ пользовательского опыта'],
            ],

            // IT - DevOps
            'it-devops' => [
                ['uz' => 'Linux server boshqaruvi', 'ru' => 'Администрирование Linux серверов'],
                ['uz' => 'Docker', 'ru' => 'Docker'],
                ['uz' => 'Kubernetes', 'ru' => 'Kubernetes'],
                ['uz' => 'CI/CD (Jenkins, GitLab CI)', 'ru' => 'CI/CD (Jenkins, GitLab CI)'],
                ['uz' => 'AWS/GCP/Azure', 'ru' => 'AWS/GCP/Azure'],
                ['uz' => 'Nginx/Apache', 'ru' => 'Nginx/Apache'],
                ['uz' => 'Monitoring (Grafana, Prometheus)', 'ru' => 'Мониторинг (Grafana, Prometheus)'],
                ['uz' => 'Terraform', 'ru' => 'Terraform'],
                ['uz' => 'Ansible', 'ru' => 'Ansible'],
                ['uz' => 'Tarmoq xavfsizligi', 'ru' => 'Сетевая безопасность'],
                ['uz' => 'Bash/Shell skriptlar', 'ru' => 'Bash/Shell скрипты'],
            ],

            // IT - QA
            'it-qa' => [
                ['uz' => 'Qo\'lda testlash', 'ru' => 'Ручное тестирование'],
                ['uz' => 'Avtomatlashtirilgan testlash', 'ru' => 'Автоматизированное тестирование'],
                ['uz' => 'Selenium', 'ru' => 'Selenium'],
                ['uz' => 'Postman', 'ru' => 'Postman'],
                ['uz' => 'API testlash', 'ru' => 'API тестирование'],
                ['uz' => 'Bug-report yozish', 'ru' => 'Написание баг-репортов'],
                ['uz' => 'Test-keys yaratish', 'ru' => 'Создание тест-кейсов'],
                ['uz' => 'Jira', 'ru' => 'Jira'],
                ['uz' => 'Regression testlash', 'ru' => 'Регрессионное тестирование'],
                ['uz' => 'Mobil ilovalarni testlash', 'ru' => 'Тестирование мобильных приложений'],
                ['uz' => 'SQL so\'rovlar', 'ru' => 'SQL запросы'],
            ],

            // IT - Other
            'it-other' => [
                ['uz' => 'Texnik yordam (IT support)', 'ru' => 'Техническая поддержка (IT support)'],
                ['uz' => 'Kompyuter ta\'mirlash', 'ru' => 'Ремонт компьютеров'],
                ['uz' => 'Tarmoq sozlash', 'ru' => 'Настройка сетей'],
                ['uz' => '1C dasturlash', 'ru' => 'Программирование 1С'],
                ['uz' => 'Ma\'lumotlarni tahlil qilish', 'ru' => 'Анализ данных'],
                ['uz' => 'Excel (ilg\'or)', 'ru' => 'Excel (продвинутый)'],
                ['uz' => 'ERP tizimlar', 'ru' => 'ERP системы'],
                ['uz' => 'Kiberxavfsizlik', 'ru' => 'Кибербезопасность'],
                ['uz' => 'Telegram bot yaratish', 'ru' => 'Создание Telegram ботов'],
                ['uz' => 'WordPress', 'ru' => 'WordPress'],
                ['uz' => 'AI va neyrosetlar', 'ru' => 'AI и нейросети'],
                ['uz' => 'Printer/skaner ta\'mirlash', 'ru' => 'Ремонт принтеров/сканеров'],
            ],

            // =============================================
            // 2. Sales (Parent)
            // =============================================
            'sales' => [
                ['uz' => 'Mijozlar bilan muloqot', 'ru' => 'Общение с клиентами'],
                ['uz' => 'Sotuv texnikasi', 'ru' => 'Техника продаж'],
                ['uz' => 'Kassa apparati bilan ishlash', 'ru' => 'Работа с кассовым аппаратом'],
                ['uz' => 'Tovarlarni joylashtirish', 'ru' => 'Выкладка товаров'],
                ['uz' => 'Inventarizatsiya', 'ru' => 'Инвентаризация'],
                ['uz' => 'CRM tizimlar', 'ru' => 'CRM системы'],
            ],

            // Sales - Shop
            'sales-shop' => [
                ['uz' => 'Chakana savdo tajribasi', 'ru' => 'Опыт розничной торговли'],
                ['uz' => 'Kassa apparati bilan ishlash', 'ru' => 'Работа с кассовым аппаратом'],
                ['uz' => 'Tovarlarni joylashtirish (merchandising)', 'ru' => 'Выкладка товаров (мерчандайзинг)'],
                ['uz' => 'Mijozlarga maslahat berish', 'ru' => 'Консультирование клиентов'],
                ['uz' => 'Inventarizatsiya o\'tkazish', 'ru' => 'Проведение инвентаризации'],
                ['uz' => 'Tovar qabul qilish', 'ru' => 'Приёмка товара'],
                ['uz' => '1C Savdo', 'ru' => '1С Торговля'],
                ['uz' => 'Shtrix-kod skaneri bilan ishlash', 'ru' => 'Работа со сканером штрих-кодов'],
                ['uz' => 'Oziq-ovqat mahsulotlari bo\'yicha bilim', 'ru' => 'Знание продовольственных товаров'],
                ['uz' => 'Elektron to\'lov tizimlarini bilish', 'ru' => 'Знание электронных платёжных систем'],
            ],

            // Sales - Market
            'sales-market' => [
                ['uz' => 'Bozor savdosi tajribasi', 'ru' => 'Опыт рыночной торговли'],
                ['uz' => 'Narx belgilash', 'ru' => 'Ценообразование'],
                ['uz' => 'Savdolashish qobiliyati', 'ru' => 'Умение торговаться'],
                ['uz' => 'Tovar saqlash qoidalari', 'ru' => 'Правила хранения товаров'],
                ['uz' => 'Og\'zaki hisob-kitob', 'ru' => 'Устный счёт'],
                ['uz' => 'Mijozlar bilan muloqot', 'ru' => 'Общение с покупателями'],
                ['uz' => 'Mahsulotni targ\'ib qilish', 'ru' => 'Продвижение продукции'],
                ['uz' => 'Qadoqlash', 'ru' => 'Упаковка товаров'],
                ['uz' => 'Tarozida tortish', 'ru' => 'Работа с весами'],
                ['uz' => 'Kunlik hisobot yuritish', 'ru' => 'Ведение ежедневной отчётности'],
            ],

            // Sales - Consultant
            'sales-consultant' => [
                ['uz' => 'Mahsulot bo\'yicha bilim', 'ru' => 'Знание продукта'],
                ['uz' => 'Sotuv texnikasi (SPIN, AIDA)', 'ru' => 'Техника продаж (SPIN, AIDA)'],
                ['uz' => 'Mijoz ehtiyojlarini aniqlash', 'ru' => 'Выявление потребностей клиента'],
                ['uz' => 'CRM tizimida ishlash', 'ru' => 'Работа в CRM системе'],
                ['uz' => 'Prezentatsiya qilish', 'ru' => 'Проведение презентаций'],
                ['uz' => 'E\'tirozlar bilan ishlash', 'ru' => 'Работа с возражениями'],
                ['uz' => 'Shartnoma tuzish', 'ru' => 'Составление договоров'],
                ['uz' => 'Telefon orqali sotuv', 'ru' => 'Телефонные продажи'],
                ['uz' => 'B2B sotuv', 'ru' => 'B2B продажи'],
                ['uz' => 'Hisobot tayyorlash', 'ru' => 'Подготовка отчётов'],
            ],

            // Sales - Cashier
            'sales-cashier' => [
                ['uz' => 'Kassa apparati bilan ishlash', 'ru' => 'Работа с кассовым аппаратом'],
                ['uz' => 'POS-terminal bilan ishlash', 'ru' => 'Работа с POS-терминалом'],
                ['uz' => 'Naqd va naqdsiz to\'lov', 'ru' => 'Наличный и безналичный расчёт'],
                ['uz' => 'Soliq hujjatlarini bilish', 'ru' => 'Знание налоговых документов'],
                ['uz' => 'Pulni tez sanash', 'ru' => 'Быстрый подсчёт денег'],
                ['uz' => 'Smena yakunlash hisoboti', 'ru' => 'Отчёт по закрытию смены'],
                ['uz' => 'Qaytarish operatsiyalari', 'ru' => 'Операции по возврату'],
                ['uz' => 'Diqqatlilik va aniqlik', 'ru' => 'Внимательность и точность'],
                ['uz' => 'Xaridorlar bilan muloqot', 'ru' => 'Общение с покупателями'],
                ['uz' => 'Click/Payme to\'lov tizimlari', 'ru' => 'Платёжные системы Click/Payme'],
            ],

            // Sales - Wholesale
            'sales-wholesale' => [
                ['uz' => 'Ulgurji savdo tajribasi', 'ru' => 'Опыт оптовой торговли'],
                ['uz' => 'Ta\'minotchilar bilan muzokaralar', 'ru' => 'Переговоры с поставщиками'],
                ['uz' => 'Logistika tushunchasi', 'ru' => 'Понимание логистики'],
                ['uz' => 'Kontraktlar bilan ishlash', 'ru' => 'Работа с контрактами'],
                ['uz' => 'Excel/1C bilan ishlash', 'ru' => 'Работа с Excel/1С'],
                ['uz' => 'Debitorlik qarzlarni nazorat qilish', 'ru' => 'Контроль дебиторской задолженности'],
                ['uz' => 'Bozor tahlili', 'ru' => 'Анализ рынка'],
                ['uz' => 'Narxni shakllantirish', 'ru' => 'Формирование цен'],
                ['uz' => 'Yirik partiyalar bilan ishlash', 'ru' => 'Работа с крупными партиями'],
                ['uz' => 'Import/eksport hujjatlari', 'ru' => 'Документы по импорту/экспорту'],
            ],

            // =============================================
            // 3. Food (Parent)
            // =============================================
            'food' => [
                ['uz' => 'Sanitariya-gigiena qoidalari', 'ru' => 'Санитарно-гигиенические нормы'],
                ['uz' => 'Oziq-ovqat xavfsizligi', 'ru' => 'Безопасность пищевых продуктов'],
                ['uz' => 'Jamoaviy ish', 'ru' => 'Командная работа'],
                ['uz' => 'Tezkor ishlash', 'ru' => 'Быстрая работа'],
                ['uz' => 'Tozalikni saqlash', 'ru' => 'Поддержание чистоты'],
                ['uz' => 'Tibbiy kitobcha', 'ru' => 'Медицинская книжка'],
            ],

            // Food - Cook
            'food-cook' => [
                ['uz' => 'O\'zbek oshxonasi', 'ru' => 'Узбекская кухня'],
                ['uz' => 'Yevropa oshxonasi', 'ru' => 'Европейская кухня'],
                ['uz' => 'Sharq oshxonasi', 'ru' => 'Восточная кухня'],
                ['uz' => 'Turk oshxonasi', 'ru' => 'Турецкая кухня'],
                ['uz' => 'Kores oshxonasi', 'ru' => 'Корейская кухня'],
                ['uz' => 'Yapon oshxonasi (sushi, rolllar)', 'ru' => 'Японская кухня (суши, роллы)'],
                ['uz' => 'Italyan oshxonasi (pasta, pitsa)', 'ru' => 'Итальянская кухня (паста, пицца)'],
                ['uz' => 'Gruzin oshxonasi', 'ru' => 'Грузинская кухня'],
                ['uz' => 'Xitoy oshxonasi', 'ru' => 'Китайская кухня'],
                ['uz' => 'Arab oshxonasi', 'ru' => 'Арабская кухня'],
                ['uz' => 'Hind oshxonasi', 'ru' => 'Индийская кухня'],
                ['uz' => 'Fast food tayyorlash', 'ru' => 'Приготовление фастфуда'],
                ['uz' => 'Osh tayyorlash', 'ru' => 'Приготовление плова'],
                ['uz' => 'Kabob va grill tayyorlash', 'ru' => 'Приготовление шашлыка и гриля'],
                ['uz' => 'Baliq tayyorlash', 'ru' => 'Приготовление рыбных блюд'],
                ['uz' => 'Go\'sht tayyorlash', 'ru' => 'Приготовление мяса'],
                ['uz' => 'Salatlar va sovuq taomlar', 'ru' => 'Салаты и холодные закуски'],
                ['uz' => 'Sho\'rva va qaynoqlar', 'ru' => 'Супы и горячие блюда'],
                ['uz' => 'Tandirda pishirish', 'ru' => 'Выпечка в тандыре'],
                ['uz' => 'Menyu tuzish', 'ru' => 'Составление меню'],
                ['uz' => 'Food cost hisoblash', 'ru' => 'Расчёт фудкоста'],
                ['uz' => 'Katta hajmli taom tayyorlash (katering)', 'ru' => 'Приготовление в больших объёмах (кейтеринг)'],
                ['uz' => 'Oshxona jihozlari bilan ishlash', 'ru' => 'Работа с кухонным оборудованием'],
                ['uz' => 'Porsiyalash va bezash', 'ru' => 'Порционирование и оформление блюд'],
                ['uz' => 'Sanitariya normalari', 'ru' => 'Санитарные нормы'],
            ],

            // Food - Waiter
            'food-waiter' => [
                ['uz' => 'Mehmonlarga xizmat ko\'rsatish', 'ru' => 'Обслуживание гостей'],
                ['uz' => 'Menyu bilimi', 'ru' => 'Знание меню'],
                ['uz' => 'Buyurtmalarni qabul qilish', 'ru' => 'Приём заказов'],
                ['uz' => 'Stol bezash', 'ru' => 'Сервировка стола'],
                ['uz' => 'R-Keeper/iiko', 'ru' => 'R-Keeper/iiko'],
                ['uz' => 'Til bilishi (rus/ingliz)', 'ru' => 'Знание языков (русский/английский)'],
                ['uz' => 'Konfliktlarni hal qilish', 'ru' => 'Разрешение конфликтов'],
                ['uz' => 'Banket xizmati', 'ru' => 'Банкетное обслуживание'],
                ['uz' => 'Tezkor xizmat ko\'rsatish', 'ru' => 'Быстрое обслуживание'],
                ['uz' => 'Ichimliklar bo\'yicha bilim', 'ru' => 'Знание напитков'],
                ['uz' => 'Kalyon (qalyon) xizmati', 'ru' => 'Кальянное обслуживание'],
                ['uz' => 'Kassa va POS-terminal bilan ishlash', 'ru' => 'Работа с кассой и POS-терминалом'],
            ],

            // Food - Barista
            'food-barista' => [
                ['uz' => 'Kofe tayyorlash', 'ru' => 'Приготовление кофе'],
                ['uz' => 'Espresso mashina bilan ishlash', 'ru' => 'Работа с эспрессо-машиной'],
                ['uz' => 'Latte-art', 'ru' => 'Латте-арт'],
                ['uz' => 'Kofe navlari bilimi', 'ru' => 'Знание сортов кофе'],
                ['uz' => 'Sovuq ichimliklar tayyorlash', 'ru' => 'Приготовление холодных напитков'],
                ['uz' => 'Smoothie va fresh tayyorlash', 'ru' => 'Приготовление смузи и фрешей'],
                ['uz' => 'Choy tayyorlash', 'ru' => 'Приготовление чая'],
                ['uz' => 'Desertlar bilan ishlash', 'ru' => 'Работа с десертами'],
                ['uz' => 'Kassa bilan ishlash', 'ru' => 'Работа с кассой'],
                ['uz' => 'Tozalik va gigiena', 'ru' => 'Чистота и гигиена'],
                ['uz' => 'Mehmonlar bilan muloqot', 'ru' => 'Общение с гостями'],
            ],

            // Food - Baker
            'food-baker' => [
                ['uz' => 'Non yopish', 'ru' => 'Выпечка хлеба'],
                ['uz' => 'Patir va somsa tayyorlash', 'ru' => 'Приготовление лепёшек и самсы'],
                ['uz' => 'Tort va pirojniy tayyorlash', 'ru' => 'Приготовление тортов и пирожных'],
                ['uz' => 'Xamir tayyorlash', 'ru' => 'Приготовление теста'],
                ['uz' => 'Tandirda ishlash', 'ru' => 'Работа с тандыром'],
                ['uz' => 'Pechda pishirish', 'ru' => 'Работа с печью'],
                ['uz' => 'Qandolatchilik', 'ru' => 'Кондитерское дело'],
                ['uz' => 'Reseptlar bilimi', 'ru' => 'Знание рецептур'],
                ['uz' => 'Pishiriqlar (burek, baklava, kruassan)', 'ru' => 'Выпечка (бурек, баклава, круассан)'],
                ['uz' => 'Pechenye va pechak tayyorlash', 'ru' => 'Приготовление печенья и кексов'],
                ['uz' => 'Bezash va dekoratsiya', 'ru' => 'Оформление и декорация'],
                ['uz' => 'Mahsulot sifatini nazorat qilish', 'ru' => 'Контроль качества продукции'],
                ['uz' => 'Sanitariya qoidalari', 'ru' => 'Санитарные правила'],
            ],

            // Food - Dishwasher
            'food-dishwasher' => [
                ['uz' => 'Idish-tovoqlarni yuvish', 'ru' => 'Мытьё посуды'],
                ['uz' => 'Oshxonani tozalash', 'ru' => 'Уборка кухни'],
                ['uz' => 'Idish yuvish mashinasi bilan ishlash', 'ru' => 'Работа с посудомоечной машиной'],
                ['uz' => 'Tozalik vositalarini bilish', 'ru' => 'Знание чистящих средств'],
                ['uz' => 'Tezkor ishlash', 'ru' => 'Быстрая работа'],
                ['uz' => 'Sanitariya-gigiena normalari', 'ru' => 'Санитарно-гигиенические нормы'],
                ['uz' => 'Idishlarni saralash', 'ru' => 'Сортировка посуды'],
                ['uz' => 'Jihozlarni tozalash', 'ru' => 'Чистка оборудования'],
                ['uz' => 'Tartiblilik', 'ru' => 'Аккуратность'],
                ['uz' => 'Jismoniy chidamlilik', 'ru' => 'Физическая выносливость'],
            ],

            // =============================================
            // 4. Driver (Parent)
            // =============================================
            'driver' => [
                ['uz' => 'Haydovchilik guvohnomasi', 'ru' => 'Водительское удостоверение'],
                ['uz' => 'Yo\'l harakati qoidalari', 'ru' => 'Правила дорожного движения'],
                ['uz' => 'Shahar yo\'llarini bilish', 'ru' => 'Знание городских дорог'],
                ['uz' => 'Avtomobilni texnik ko\'rik', 'ru' => 'Техосмотр автомобиля'],
                ['uz' => 'GPS-navigator bilan ishlash', 'ru' => 'Работа с GPS-навигатором'],
                ['uz' => 'Bexatar haydash', 'ru' => 'Безопасное вождение'],
            ],

            // Driver - Taxi
            'driver-taxi' => [
                ['uz' => 'B toifali guvohnoma', 'ru' => 'Права категории B'],
                ['uz' => 'Shahar ko\'chalalarini bilish', 'ru' => 'Знание улиц города'],
                ['uz' => 'Yandex Go/Uber ilovasi', 'ru' => 'Приложение Yandex Go/Uber'],
                ['uz' => 'MyTaxi/InDrive ilovasi', 'ru' => 'Приложение MyTaxi/InDrive'],
                ['uz' => 'Yo\'lovchilar bilan muloqot', 'ru' => 'Общение с пассажирами'],
                ['uz' => 'Avtomobil texnik xizmati', 'ru' => 'Техобслуживание автомобиля'],
                ['uz' => 'Tezkor navigatsiya', 'ru' => 'Быстрая навигация'],
                ['uz' => 'Tozalikni saqlash', 'ru' => 'Поддержание чистоты'],
                ['uz' => 'Naqd va naqdsiz to\'lov', 'ru' => 'Наличная и безналичная оплата'],
                ['uz' => 'Punktuallik', 'ru' => 'Пунктуальность'],
            ],

            // Driver - Truck
            'driver-truck' => [
                ['uz' => 'C/CE toifali guvohnoma', 'ru' => 'Права категории C/CE'],
                ['uz' => 'Yuk tashish tajribasi', 'ru' => 'Опыт грузоперевозок'],
                ['uz' => 'Uzoq masofaga haydash', 'ru' => 'Вождение на дальние расстояния'],
                ['uz' => 'Yuk ortish/tushirish', 'ru' => 'Погрузка/разгрузка'],
                ['uz' => 'Transport hujjatlari', 'ru' => 'Транспортная документация'],
                ['uz' => 'Tir haydash', 'ru' => 'Вождение фуры'],
                ['uz' => 'Yuk xavfsizligi', 'ru' => 'Безопасность груза'],
                ['uz' => 'Texnik nosozliklarni tuzatish', 'ru' => 'Устранение технических неисправностей'],
                ['uz' => 'Xalqaro yo\'nalishlar', 'ru' => 'Международные маршруты'],
                ['uz' => 'Tachograf bilan ishlash', 'ru' => 'Работа с тахографом'],
            ],

            // Driver - Delivery
            'driver-delivery' => [
                ['uz' => 'Yetkazib berish tajribasi', 'ru' => 'Опыт доставки'],
                ['uz' => 'Shahar bo\'ylab navigatsiya', 'ru' => 'Навигация по городу'],
                ['uz' => 'Buyurtmalarni boshqarish', 'ru' => 'Управление заказами'],
                ['uz' => 'Tezkor yetkazib berish', 'ru' => 'Быстрая доставка'],
                ['uz' => 'Mijozlar bilan muloqot', 'ru' => 'Общение с клиентами'],
                ['uz' => 'Moped/velosiped haydash', 'ru' => 'Вождение мопеда/велосипеда'],
                ['uz' => 'Ilovalar bilan ishlash (Uzum, Express24, Wolt)', 'ru' => 'Работа с приложениями (Uzum, Express24, Wolt)'],
                ['uz' => 'Naqd pul bilan hisob-kitob', 'ru' => 'Работа с наличными'],
                ['uz' => 'Buyurtmani saqlash', 'ru' => 'Сохранность заказа'],
                ['uz' => 'Vaqtni boshqarish', 'ru' => 'Управление временем'],
            ],

            // Driver - Personal
            'driver-personal' => [
                ['uz' => 'B toifali guvohnoma', 'ru' => 'Права категории B'],
                ['uz' => 'Shaxsiy haydovchilik tajribasi', 'ru' => 'Опыт личного вождения'],
                ['uz' => 'Punktuallik', 'ru' => 'Пунктуальность'],
                ['uz' => 'Avtomobilga g\'amxo\'rlik', 'ru' => 'Уход за автомобилем'],
                ['uz' => 'Maxfiylikni saqlash', 'ru' => 'Соблюдение конфиденциальности'],
                ['uz' => 'VIP mehmonlar bilan ishlash', 'ru' => 'Работа с VIP-гостями'],
                ['uz' => 'Xavfsiz haydash uslubi', 'ru' => 'Безопасный стиль вождения'],
                ['uz' => 'Tashqi ko\'rinishga e\'tibor', 'ru' => 'Внимание к внешнему виду'],
                ['uz' => 'Turli avtomobillar bilan ishlash', 'ru' => 'Работа с различными автомобилями'],
                ['uz' => 'Odoblilik va madaniyatlilik', 'ru' => 'Вежливость и культурность'],
            ],

            // =============================================
            // 5. Construction (Parent)
            // =============================================
            'construction' => [
                ['uz' => 'Qurilish xavfsizlik qoidalari', 'ru' => 'Правила строительной безопасности'],
                ['uz' => 'Qurilish asboblari bilan ishlash', 'ru' => 'Работа со строительными инструментами'],
                ['uz' => 'Chizmalarni o\'qish', 'ru' => 'Чтение чертежей'],
                ['uz' => 'Qurilish materiallari bilimi', 'ru' => 'Знание строительных материалов'],
                ['uz' => 'Jismoniy chidamlilik', 'ru' => 'Физическая выносливость'],
                ['uz' => 'Jamoaviy ish', 'ru' => 'Командная работа'],
            ],

            // Construction - Builder
            'construction-builder' => [
                ['uz' => 'G\'isht terish', 'ru' => 'Кирпичная кладка'],
                ['uz' => 'Beton ishlari', 'ru' => 'Бетонные работы'],
                ['uz' => 'Poydevor qurish', 'ru' => 'Заливка фундамента'],
                ['uz' => 'Devorlarni suvatash', 'ru' => 'Штукатурка стен'],
                ['uz' => 'Kafel va plitka yotqizish', 'ru' => 'Укладка кафеля и плитки'],
                ['uz' => 'Qurilish chizmalarini o\'qish', 'ru' => 'Чтение строительных чертежей'],
                ['uz' => 'Armaturaband ishlari', 'ru' => 'Арматурные работы'],
                ['uz' => 'Tom yopish', 'ru' => 'Кровельные работы'],
                ['uz' => 'Opaloq qilish', 'ru' => 'Опалубочные работы'],
                ['uz' => 'Qurilish jihozlari bilan ishlash', 'ru' => 'Работа со строительной техникой'],
                ['uz' => 'Xavfsizlik texnikasi', 'ru' => 'Техника безопасности'],
            ],

            // Construction - Electrician
            'construction-electrician' => [
                ['uz' => 'Elektr montaj ishlari', 'ru' => 'Электромонтажные работы'],
                ['uz' => 'Elektr sxemalarni o\'qish', 'ru' => 'Чтение электросхем'],
                ['uz' => 'Simlarni ulash', 'ru' => 'Монтаж проводки'],
                ['uz' => 'Rozetka va o\'chirg\'ich o\'rnatish', 'ru' => 'Установка розеток и выключателей'],
                ['uz' => 'Elektr shchitlarni yig\'ish', 'ru' => 'Сборка электрощитов'],
                ['uz' => 'Yoritish tizimlarini o\'rnatish', 'ru' => 'Установка систем освещения'],
                ['uz' => 'Elektr xavfsizlik qoidalari', 'ru' => 'Правила электробезопасности'],
                ['uz' => 'Kuchsiz toklar tizimi', 'ru' => 'Слаботочные системы'],
                ['uz' => 'Nosozliklarni aniqlash', 'ru' => 'Диагностика неисправностей'],
                ['uz' => 'Sanoat elektr jihozlari', 'ru' => 'Промышленное электрооборудование'],
            ],

            // Construction - Plumber
            'construction-plumber' => [
                ['uz' => 'Suv trubalarini o\'rnatish', 'ru' => 'Монтаж водопроводных труб'],
                ['uz' => 'Kanalizatsiya tizimi', 'ru' => 'Канализационные системы'],
                ['uz' => 'Isitish tizimlari', 'ru' => 'Системы отопления'],
                ['uz' => 'Konditsioner o\'rnatish va ta\'mirlash', 'ru' => 'Установка и ремонт кондиционеров'],
                ['uz' => 'Santexnika jihozlarini o\'rnatish', 'ru' => 'Установка сантехнического оборудования'],
                ['uz' => 'Quvurlarni payvandlash', 'ru' => 'Сварка труб'],
                ['uz' => 'Oqish va nosozliklarni tuzatish', 'ru' => 'Устранение течей и неисправностей'],
                ['uz' => 'Polipropilendan ishlash', 'ru' => 'Работа с полипропиленом'],
                ['uz' => 'Issiq suv tizimi', 'ru' => 'Система горячего водоснабжения'],
                ['uz' => 'Filtr va nasos o\'rnatish', 'ru' => 'Установка фильтров и насосов'],
                ['uz' => 'Gaz jihozlari bilan ishlash', 'ru' => 'Работа с газовым оборудованием'],
            ],

            // Construction - Painter
            'construction-painter' => [
                ['uz' => 'Devor bo\'yash', 'ru' => 'Покраска стен'],
                ['uz' => 'Shpaklyovka qilish', 'ru' => 'Шпаклёвка'],
                ['uz' => 'Oboi yopishtirish', 'ru' => 'Поклейка обоев'],
                ['uz' => 'Dekorativ bo\'yash', 'ru' => 'Декоративная покраска'],
                ['uz' => 'Yuzani tayyorlash', 'ru' => 'Подготовка поверхности'],
                ['uz' => 'Bo\'yoqlar turlarini bilish', 'ru' => 'Знание видов красок'],
                ['uz' => 'Pnevmatik pulverizator bilan ishlash', 'ru' => 'Работа с пневматическим краскопультом'],
                ['uz' => 'Shift va devor bezash', 'ru' => 'Отделка потолков и стен'],
                ['uz' => 'Gips karton bilan ishlash', 'ru' => 'Работа с гипсокартоном'],
                ['uz' => 'Toza va sifatli ish', 'ru' => 'Чистая и качественная работа'],
            ],

            // Construction - Welder
            'construction-welder' => [
                ['uz' => 'Elektr payvandlash', 'ru' => 'Электросварка'],
                ['uz' => 'Gaz payvandlash', 'ru' => 'Газосварка'],
                ['uz' => 'Argon payvandlash', 'ru' => 'Аргонная сварка'],
                ['uz' => 'Yarim avtomat payvandlash', 'ru' => 'Полуавтоматическая сварка'],
                ['uz' => 'Metall konstruktsiyalar', 'ru' => 'Металлоконструкции'],
                ['uz' => 'Chizmalarni o\'qish', 'ru' => 'Чтение чертежей'],
                ['uz' => 'Metallni kesish', 'ru' => 'Резка металла'],
                ['uz' => 'Nerjaveyuschi po\'lat bilan ishlash', 'ru' => 'Работа с нержавеющей сталью'],
                ['uz' => 'Payvandlash xavfsizligi', 'ru' => 'Безопасность сварочных работ'],
                ['uz' => 'Quvurlarni payvandlash', 'ru' => 'Сварка трубопроводов'],
            ],

            // Construction - Carpenter
            'construction-carpenter' => [
                ['uz' => 'Yog\'och bilan ishlash', 'ru' => 'Работа с деревом'],
                ['uz' => 'Eshik va deraza o\'rnatish', 'ru' => 'Установка дверей и окон'],
                ['uz' => 'Mebel yasash', 'ru' => 'Изготовление мебели'],
                ['uz' => 'Pol yotqizish (laminat, parket)', 'ru' => 'Укладка пола (ламинат, паркет)'],
                ['uz' => 'Yog\'och asboblar bilan ishlash', 'ru' => 'Работа с деревообрабатывающим инструментом'],
                ['uz' => 'Chizmalar bo\'yicha ishlash', 'ru' => 'Работа по чертежам'],
                ['uz' => 'Opaloq yasash', 'ru' => 'Изготовление опалубки'],
                ['uz' => 'Yog\'ochni qayta ishlash', 'ru' => 'Обработка древесины'],
                ['uz' => 'Ichki bezak ishlari', 'ru' => 'Внутренняя отделка'],
                ['uz' => 'O\'lchov asboblari bilan ishlash', 'ru' => 'Работа с измерительными инструментами'],
            ],

            // =============================================
            // 6. Beauty (Parent)
            // =============================================
            'beauty' => [
                ['uz' => 'Mijozlar bilan muloqot', 'ru' => 'Общение с клиентами'],
                ['uz' => 'Sanitariya-gigiena normalari', 'ru' => 'Санитарно-гигиенические нормы'],
                ['uz' => 'Ish joyini tozalash', 'ru' => 'Содержание рабочего места в чистоте'],
                ['uz' => 'Mahsulotlar bilimi', 'ru' => 'Знание продукции'],
                ['uz' => 'Estetik did', 'ru' => 'Эстетический вкус'],
                ['uz' => 'Trendlarni bilish', 'ru' => 'Знание трендов'],
            ],

            // Beauty - Hairdresser
            'beauty-hairdresser' => [
                ['uz' => 'Erkaklar soch turmagi', 'ru' => 'Мужские стрижки'],
                ['uz' => 'Ayollar soch turmagi', 'ru' => 'Женские стрижки'],
                ['uz' => 'Sochni bo\'yash', 'ru' => 'Окрашивание волос'],
                ['uz' => 'Soch turmaklash (ukladka)', 'ru' => 'Укладка волос'],
                ['uz' => 'Soch davolash', 'ru' => 'Лечение волос'],
                ['uz' => 'Melirovanie/balayaj', 'ru' => 'Мелирование/балаяж'],
                ['uz' => 'Bolalar soch turmagi', 'ru' => 'Детские стрижки'],
                ['uz' => 'Soqol olish va shakl berish', 'ru' => 'Бритьё и моделирование бороды'],
                ['uz' => 'Soch uzaytirish (narashchivanie)', 'ru' => 'Наращивание волос'],
                ['uz' => 'Keratin davolash', 'ru' => 'Кератиновое выпрямление'],
                ['uz' => 'To\'y soch turmagi', 'ru' => 'Свадебные причёски'],
                ['uz' => 'Kiprik uzaytirish', 'ru' => 'Наращивание ресниц'],
            ],

            // Beauty - Manicure
            'beauty-manicure' => [
                ['uz' => 'Klassik manikur', 'ru' => 'Классический маникюр'],
                ['uz' => 'Apparat manikuri', 'ru' => 'Аппаратный маникюр'],
                ['uz' => 'Gel-lak qoplash', 'ru' => 'Покрытие гель-лаком'],
                ['uz' => 'Tirnoq dizayni', 'ru' => 'Дизайн ногтей'],
                ['uz' => 'Tirnoq uzaytirish', 'ru' => 'Наращивание ногтей'],
                ['uz' => 'Pedikur', 'ru' => 'Педикюр'],
                ['uz' => 'Sterilizatsiya', 'ru' => 'Стерилизация'],
                ['uz' => 'Tirnoq davolash', 'ru' => 'Лечение ногтей'],
                ['uz' => 'Stamping va slayder', 'ru' => 'Стемпинг и слайдеры'],
                ['uz' => 'Asboblarni parvarish qilish', 'ru' => 'Уход за инструментами'],
            ],

            // Beauty - Cosmetologist
            'beauty-cosmetologist' => [
                ['uz' => 'Yuz tozalash', 'ru' => 'Чистка лица'],
                ['uz' => 'Teri parvarishi', 'ru' => 'Уход за кожей'],
                ['uz' => 'Inyeksion kosmetologiya', 'ru' => 'Инъекционная косметология'],
                ['uz' => 'Apparat kosmetologiyasi', 'ru' => 'Аппаратная косметология'],
                ['uz' => 'Piling va maskalar', 'ru' => 'Пилинги и маски'],
                ['uz' => 'Lazer epilyatsiya', 'ru' => 'Лазерная эпиляция'],
                ['uz' => 'Teri diagnostikasi', 'ru' => 'Диагностика кожи'],
                ['uz' => 'Anti-age protseduralar', 'ru' => 'Антивозрастные процедуры'],
                ['uz' => 'Kosmetik preparatlar bilimi', 'ru' => 'Знание косметических препаратов'],
                ['uz' => 'Mexanik tozalash', 'ru' => 'Механическая чистка'],
            ],

            // Beauty - Masseur
            'beauty-masseur' => [
                ['uz' => 'Klassik massaj', 'ru' => 'Классический массаж'],
                ['uz' => 'Davolash massaji', 'ru' => 'Лечебный массаж'],
                ['uz' => 'Sport massaji', 'ru' => 'Спортивный массаж'],
                ['uz' => 'Antisellyulit massaji', 'ru' => 'Антицеллюлитный массаж'],
                ['uz' => 'Yuz massaji', 'ru' => 'Массаж лица'],
                ['uz' => 'Orqa massaji', 'ru' => 'Массаж спины'],
                ['uz' => 'Anatomiya bilimi', 'ru' => 'Знание анатомии'],
                ['uz' => 'SPA protseduralar', 'ru' => 'SPA процедуры'],
                ['uz' => 'Limfodrenaj massaji', 'ru' => 'Лимфодренажный массаж'],
                ['uz' => 'Bolalar massaji', 'ru' => 'Детский массаж'],
            ],

            // Beauty - Nurse
            'beauty-nurse' => [
                ['uz' => 'Tibbiy ma\'lumot', 'ru' => 'Медицинское образование'],
                ['uz' => 'In\'ektsiya qilish', 'ru' => 'Проведение инъекций'],
                ['uz' => 'Dropper qo\'yish', 'ru' => 'Постановка капельниц'],
                ['uz' => 'Bemorlarni parvarish qilish', 'ru' => 'Уход за пациентами'],
                ['uz' => 'Bosim va harorat o\'lchash', 'ru' => 'Измерение давления и температуры'],
                ['uz' => 'Yaralarni bog\'lash', 'ru' => 'Перевязка ран'],
                ['uz' => 'Dori-darmonlar bilimi', 'ru' => 'Знание лекарственных препаратов'],
                ['uz' => 'Tibbiy hujjatlar yuritish', 'ru' => 'Ведение медицинской документации'],
                ['uz' => 'Birinchi tibbiy yordam', 'ru' => 'Первая медицинская помощь'],
                ['uz' => 'Sterillik qoidalari', 'ru' => 'Правила стерильности'],
            ],

            // Beauty - Pharmacist
            'beauty-pharmacist' => [
                ['uz' => 'Farmakologiya bilimi', 'ru' => 'Знание фармакологии'],
                ['uz' => 'Dori-darmonlarni bilish', 'ru' => 'Знание лекарственных средств'],
                ['uz' => 'Retseptlar bilan ishlash', 'ru' => 'Работа с рецептами'],
                ['uz' => 'Dorixona jihozlari', 'ru' => 'Аптечное оборудование'],
                ['uz' => 'Mijozlarga maslahat berish', 'ru' => 'Консультирование клиентов'],
                ['uz' => 'Dorilarni saqlash qoidalari', 'ru' => 'Правила хранения лекарств'],
                ['uz' => '1C Dorixona', 'ru' => '1С Аптека'],
                ['uz' => 'Sertifikatlar va litsenziyalar', 'ru' => 'Сертификаты и лицензии'],
                ['uz' => 'BADlar va vitaminlar', 'ru' => 'БАДы и витамины'],
                ['uz' => 'Tibbiy terminologiya', 'ru' => 'Медицинская терминология'],
            ],

            // =============================================
            // 7. Education (Parent)
            // =============================================
            'education' => [
                ['uz' => 'O\'qitish metodikasi', 'ru' => 'Методика преподавания'],
                ['uz' => 'Sabr-toqat va chidamlilik', 'ru' => 'Терпение и выдержка'],
                ['uz' => 'Muloqot ko\'nikmalari', 'ru' => 'Коммуникативные навыки'],
                ['uz' => 'Pedagogik ta\'lim', 'ru' => 'Педагогическое образование'],
                ['uz' => 'Zamonaviy o\'qitish texnologiyalari', 'ru' => 'Современные образовательные технологии'],
                ['uz' => 'Bolalar bilan ishlash tajribasi', 'ru' => 'Опыт работы с детьми'],
            ],

            // Education - Teacher
            'education-teacher' => [
                ['uz' => 'Fan bo\'yicha bilim (matematika, fizika va boshqalar)', 'ru' => 'Знание предмета (математика, физика и др.)'],
                ['uz' => 'Dars rejasi tuzish', 'ru' => 'Составление плана урока'],
                ['uz' => 'Sinf boshqaruvi', 'ru' => 'Управление классом'],
                ['uz' => 'O\'quvchilarni baholash', 'ru' => 'Оценивание учеников'],
                ['uz' => 'Ota-onalar bilan ish', 'ru' => 'Работа с родителями'],
                ['uz' => 'Interaktiv dars o\'tish', 'ru' => 'Проведение интерактивных уроков'],
                ['uz' => 'O\'quv dasturlarni bilish', 'ru' => 'Знание учебных программ'],
                ['uz' => 'Maktab hujjatlari', 'ru' => 'Школьная документация'],
                ['uz' => 'Kompyuter savodxonligi', 'ru' => 'Компьютерная грамотность'],
                ['uz' => 'Tarbiyaviy ish', 'ru' => 'Воспитательная работа'],
            ],

            // Education - Tutor
            'education-tutor' => [
                ['uz' => 'Individual yondashuv', 'ru' => 'Индивидуальный подход'],
                ['uz' => 'Imtihonlarga tayyorlash (DTM, IELTS, SAT)', 'ru' => 'Подготовка к экзаменам (ДТМ, IELTS, SAT)'],
                ['uz' => 'Ingliz tili o\'qitish', 'ru' => 'Преподавание английского языка'],
                ['uz' => 'Matematika o\'qitish', 'ru' => 'Преподавание математики'],
                ['uz' => 'Rus tili o\'qitish', 'ru' => 'Преподавание русского языка'],
                ['uz' => 'Ona tili (o\'zbek) o\'qitish', 'ru' => 'Преподавание узбекского языка'],
                ['uz' => 'Kores tili o\'qitish', 'ru' => 'Преподавание корейского языка'],
                ['uz' => 'Arab tili o\'qitish', 'ru' => 'Преподавание арабского языка'],
                ['uz' => 'Fransuz tili o\'qitish', 'ru' => 'Преподавание французского языка'],
                ['uz' => 'Nemis tili o\'qitish', 'ru' => 'Преподавание немецкого языка'],
                ['uz' => 'Onlayn dars o\'tish', 'ru' => 'Проведение онлайн-уроков'],
                ['uz' => 'O\'quv materiallar tayyorlash', 'ru' => 'Подготовка учебных материалов'],
                ['uz' => 'O\'quvchi motivatsiyasi', 'ru' => 'Мотивация ученика'],
                ['uz' => 'Bilim darajasini aniqlash', 'ru' => 'Определение уровня знаний'],
            ],

            // Education - Trainer
            'education-trainer' => [
                ['uz' => 'Sport mashg\'ulotlari', 'ru' => 'Спортивные тренировки'],
                ['uz' => 'Fitnes va jismoniy tayyorgarlik', 'ru' => 'Фитнес и физическая подготовка'],
                ['uz' => 'Shaxsiy mashg\'ulotlar', 'ru' => 'Персональные тренировки'],
                ['uz' => 'Ovqatlanish maslahatlar', 'ru' => 'Консультации по питанию'],
                ['uz' => 'Guruh mashg\'ulotlari', 'ru' => 'Групповые тренировки'],
                ['uz' => 'Bolalar sport seksiyasi', 'ru' => 'Детские спортивные секции'],
                ['uz' => 'Yoga va cho\'zish', 'ru' => 'Йога и растяжка'],
                ['uz' => 'Suzish o\'qitish', 'ru' => 'Обучение плаванию'],
                ['uz' => 'Jang san\'atlari (karate, boks, taekvondo)', 'ru' => 'Боевые искусства (каратэ, бокс, тхэквондо)'],
                ['uz' => 'Birinchi tibbiy yordam', 'ru' => 'Первая медицинская помощь'],
                ['uz' => 'Sport fiziologiyasi', 'ru' => 'Спортивная физиология'],
                ['uz' => 'Mashg\'ulot rejasini tuzish', 'ru' => 'Составление плана тренировок'],
            ],

            // Education - Nanny
            'education-nanny' => [
                ['uz' => 'Bolalar bilan ishlash tajribasi', 'ru' => 'Опыт работы с детьми'],
                ['uz' => 'Bolalar rivojlanishi bilimi', 'ru' => 'Знание детского развития'],
                ['uz' => 'Bolalar ovqatini tayyorlash', 'ru' => 'Приготовление детского питания'],
                ['uz' => 'O\'yin orqali o\'qitish', 'ru' => 'Обучение через игру'],
                ['uz' => 'Gigiyena va tozalik', 'ru' => 'Гигиена и чистота'],
                ['uz' => 'Birinchi tibbiy yordam (bolalar)', 'ru' => 'Первая помощь (детская)'],
                ['uz' => 'Sabr-toqat', 'ru' => 'Терпение'],
                ['uz' => 'Maktabgacha ta\'lim dasturi', 'ru' => 'Программа дошкольного образования'],
                ['uz' => 'Ertak o\'qish va kreativlik', 'ru' => 'Чтение сказок и творчество'],
                ['uz' => 'Kun tartibini tashkil qilish', 'ru' => 'Организация режима дня'],
            ],

            // =============================================
            // 8. Finance (Parent)
            // =============================================
            'finance' => [
                ['uz' => 'Moliyaviy hisobotlar', 'ru' => 'Финансовая отчётность'],
                ['uz' => 'Soliq qonunchiligi', 'ru' => 'Налоговое законодательство'],
                ['uz' => 'Excel/Google Sheets', 'ru' => 'Excel/Google Sheets'],
                ['uz' => '1C Buxgalteriya', 'ru' => '1С Бухгалтерия'],
                ['uz' => 'Moliyaviy tahlil', 'ru' => 'Финансовый анализ'],
                ['uz' => 'O\'zbekiston soliq kodeksi', 'ru' => 'Налоговый кодекс Узбекистана'],
            ],

            // Finance - Accountant
            'finance-accountant' => [
                ['uz' => '1C Buxgalteriya', 'ru' => '1С Бухгалтерия'],
                ['uz' => 'Soliq hisoboti', 'ru' => 'Налоговая отчётность'],
                ['uz' => 'Ish haqi hisoblash', 'ru' => 'Расчёт заработной платы'],
                ['uz' => 'Buxgalteriya balansi', 'ru' => 'Бухгалтерский баланс'],
                ['uz' => 'BHMS (soliq) tizimi', 'ru' => 'Система БХМС (налоги)'],
                ['uz' => 'Birja va bank operatsiyalari', 'ru' => 'Биржевые и банковские операции'],
                ['uz' => 'Asosiy vositalar hisobi', 'ru' => 'Учёт основных средств'],
                ['uz' => 'Debitor va kreditor qarzlar', 'ru' => 'Дебиторская и кредиторская задолженность'],
                ['uz' => 'Kassa operatsiyalari', 'ru' => 'Кассовые операции'],
                ['uz' => 'Excel (ilg\'or daraja)', 'ru' => 'Excel (продвинутый уровень)'],
                ['uz' => 'Moliyaviy hujjatlar yuritish', 'ru' => 'Ведение финансовой документации'],
            ],

            // Finance - Economist
            'finance-economist' => [
                ['uz' => 'Iqtisodiy tahlil', 'ru' => 'Экономический анализ'],
                ['uz' => 'Biznes-reja tuzish', 'ru' => 'Составление бизнес-планов'],
                ['uz' => 'Budjetlashtirish', 'ru' => 'Бюджетирование'],
                ['uz' => 'Tannarxni hisoblash', 'ru' => 'Расчёт себестоимости'],
                ['uz' => 'Statistik tahlil', 'ru' => 'Статистический анализ'],
                ['uz' => 'Moliyaviy prognozlash', 'ru' => 'Финансовое прогнозирование'],
                ['uz' => 'Narx siyosati', 'ru' => 'Ценовая политика'],
                ['uz' => 'Investitsiya loyihalarni baholash', 'ru' => 'Оценка инвестиционных проектов'],
                ['uz' => 'Excel va Power BI', 'ru' => 'Excel и Power BI'],
                ['uz' => 'Iqtisodiy qonunchilik', 'ru' => 'Экономическое законодательство'],
            ],

            // Finance - Banker
            'finance-banker' => [
                ['uz' => 'Bank operatsiyalari', 'ru' => 'Банковские операции'],
                ['uz' => 'Kredit berish jarayoni', 'ru' => 'Процесс кредитования'],
                ['uz' => 'Depozit mahsulotlari', 'ru' => 'Депозитные продукты'],
                ['uz' => 'Valyuta operatsiyalari', 'ru' => 'Валютные операции'],
                ['uz' => 'Mijozlarga xizmat ko\'rsatish', 'ru' => 'Обслуживание клиентов'],
                ['uz' => 'Bank dasturlari (iABS)', 'ru' => 'Банковское ПО (iABS)'],
                ['uz' => 'Kreditga layiqlikni baholash', 'ru' => 'Оценка кредитоспособности'],
                ['uz' => 'Bank qonunchiligi', 'ru' => 'Банковское законодательство'],
                ['uz' => 'Plastik kartalar bilan ishlash', 'ru' => 'Работа с пластиковыми картами'],
                ['uz' => 'Moliyaviy xavflarni boshqarish', 'ru' => 'Управление финансовыми рисками'],
            ],

            // Finance - Auditor
            'finance-auditor' => [
                ['uz' => 'Audit o\'tkazish', 'ru' => 'Проведение аудита'],
                ['uz' => 'Ichki nazorat tizimlari', 'ru' => 'Системы внутреннего контроля'],
                ['uz' => 'Moliyaviy hisobotlarni tekshirish', 'ru' => 'Проверка финансовой отчётности'],
                ['uz' => 'MHXS (IFRS) standartlari', 'ru' => 'Стандарты МСФО (IFRS)'],
                ['uz' => 'Risk baholash', 'ru' => 'Оценка рисков'],
                ['uz' => 'Audit xulosasi yozish', 'ru' => 'Написание аудиторского заключения'],
                ['uz' => 'Soliq auditi', 'ru' => 'Налоговый аудит'],
                ['uz' => 'Analitik protseduralar', 'ru' => 'Аналитические процедуры'],
                ['uz' => 'Hujjatlarni tekshirish', 'ru' => 'Проверка документации'],
                ['uz' => '1C va buxgalteriya dasturlari', 'ru' => '1С и бухгалтерское ПО'],
            ],

            // =============================================
            // 9. Marketing (Parent)
            // =============================================
            'marketing' => [
                ['uz' => 'Marketing strategiyasi', 'ru' => 'Маркетинговая стратегия'],
                ['uz' => 'Raqamli marketing', 'ru' => 'Цифровой маркетинг'],
                ['uz' => 'Bozor tahlili', 'ru' => 'Анализ рынка'],
                ['uz' => 'Brendni rivojlantirish', 'ru' => 'Развитие бренда'],
                ['uz' => 'Kontentni rejalashtirish', 'ru' => 'Планирование контента'],
                ['uz' => 'Kreativlik', 'ru' => 'Креативность'],
            ],

            // Marketing - SMM
            'marketing-smm' => [
                ['uz' => 'Instagram boshqaruvi', 'ru' => 'Управление Instagram'],
                ['uz' => 'Telegram kanal boshqaruvi', 'ru' => 'Управление Telegram-каналом'],
                ['uz' => 'Facebook/Meta reklama', 'ru' => 'Реклама Facebook/Meta'],
                ['uz' => 'YouTube kanal boshqaruvi', 'ru' => 'Управление YouTube-каналом'],
                ['uz' => 'Kontent-reja tuzish', 'ru' => 'Составление контент-плана'],
                ['uz' => 'Stories va Reels yaratish', 'ru' => 'Создание Stories и Reels'],
                ['uz' => 'Auditoriya tahlili', 'ru' => 'Анализ аудитории'],
                ['uz' => 'Hashtaglar strategiyasi', 'ru' => 'Стратегия хештегов'],
                ['uz' => 'Engagement oshirish', 'ru' => 'Повышение вовлечённости'],
                ['uz' => 'Canva/Adobe bilan ishlash', 'ru' => 'Работа с Canva/Adobe'],
                ['uz' => 'TikTok boshqaruvi', 'ru' => 'Управление TikTok'],
                ['uz' => 'Hisobotlar va analitika', 'ru' => 'Отчёты и аналитика'],
            ],

            // Marketing - Content
            'marketing-content' => [
                ['uz' => 'Maqola va post yozish', 'ru' => 'Написание статей и постов'],
                ['uz' => 'Kopyrayting', 'ru' => 'Копирайтинг'],
                ['uz' => 'Video kontent yaratish', 'ru' => 'Создание видеоконтента'],
                ['uz' => 'Kontent-reja tuzish', 'ru' => 'Составление контент-плана'],
                ['uz' => 'Foto va video tahrirlash', 'ru' => 'Фото- и видеомонтаж'],
                ['uz' => 'SEO-optimizatsiya qilingan matnlar', 'ru' => 'SEO-оптимизированные тексты'],
                ['uz' => 'AI vositalar bilan ishlash (ChatGPT, Midjourney)', 'ru' => 'Работа с AI-инструментами (ChatGPT, Midjourney)'],
                ['uz' => 'Email-marketing', 'ru' => 'Email-маркетинг'],
                ['uz' => 'Blog yuritish', 'ru' => 'Ведение блога'],
                ['uz' => 'WordPress/CMS bilan ishlash', 'ru' => 'Работа с WordPress/CMS'],
                ['uz' => 'O\'zbek va rus tilida yozish', 'ru' => 'Написание текстов на узбекском и русском'],
            ],

            // Marketing - SEO
            'marketing-seo' => [
                ['uz' => 'On-page SEO', 'ru' => 'On-page SEO'],
                ['uz' => 'Off-page SEO', 'ru' => 'Off-page SEO'],
                ['uz' => 'Google Analytics', 'ru' => 'Google Analytics'],
                ['uz' => 'Google Search Console', 'ru' => 'Google Search Console'],
                ['uz' => 'Kalit so\'zlar tahlili', 'ru' => 'Анализ ключевых слов'],
                ['uz' => 'Texnik SEO', 'ru' => 'Технический SEO'],
                ['uz' => 'Link building', 'ru' => 'Link building'],
                ['uz' => 'SEO audit', 'ru' => 'SEO аудит'],
                ['uz' => 'Ahrefs/SEMrush', 'ru' => 'Ahrefs/SEMrush'],
                ['uz' => 'Lokal SEO', 'ru' => 'Локальный SEO'],
            ],

            // Marketing - Designer
            'marketing-designer' => [
                ['uz' => 'Adobe Photoshop', 'ru' => 'Adobe Photoshop'],
                ['uz' => 'Adobe Illustrator', 'ru' => 'Adobe Illustrator'],
                ['uz' => 'Canva', 'ru' => 'Canva'],
                ['uz' => 'Figma', 'ru' => 'Figma'],
                ['uz' => 'Banner va reklama dizayni', 'ru' => 'Дизайн баннеров и рекламы'],
                ['uz' => 'Brending (logotip, firmaviy uslub)', 'ru' => 'Брендинг (логотип, фирменный стиль)'],
                ['uz' => 'Ijtimoiy tarmoq grafikalari', 'ru' => 'Графика для соцсетей'],
                ['uz' => 'Poligrafiya dizayni', 'ru' => 'Полиграфический дизайн'],
                ['uz' => 'Motion graphics', 'ru' => 'Motion graphics'],
                ['uz' => 'Rang nazariyasi va tipografiya', 'ru' => 'Теория цвета и типографика'],
            ],

            // Marketing - PR
            'marketing-pr' => [
                ['uz' => 'Press-reliz yozish', 'ru' => 'Написание пресс-релизов'],
                ['uz' => 'OAV bilan ish', 'ru' => 'Работа со СМИ'],
                ['uz' => 'Tadbirlar tashkil qilish', 'ru' => 'Организация мероприятий'],
                ['uz' => 'Krizis kommunikatsiya', 'ru' => 'Кризисные коммуникации'],
                ['uz' => 'Jamoatchilik bilan aloqalar', 'ru' => 'Связи с общественностью'],
                ['uz' => 'Bloggerlar bilan hamkorlik', 'ru' => 'Сотрудничество с блогерами'],
                ['uz' => 'Brend obro\'sini boshqarish', 'ru' => 'Управление репутацией бренда'],
                ['uz' => 'Ijtimoiy loyihalar', 'ru' => 'Социальные проекты'],
                ['uz' => 'Nutq yozish', 'ru' => 'Написание речей'],
                ['uz' => 'Media-reja tuzish', 'ru' => 'Составление медиаплана'],
            ],

            // Marketing - Sales Operator
            'marketing-sales-operator' => [
                ['uz' => 'Telefon orqali sotuv', 'ru' => 'Телефонные продажи'],
                ['uz' => 'CRM tizimida ishlash', 'ru' => 'Работа в CRM системе'],
                ['uz' => 'Skript bo\'yicha ishlash', 'ru' => 'Работа по скрипту'],
                ['uz' => 'Buyurtmalarni qayta ishlash', 'ru' => 'Обработка заказов'],
                ['uz' => 'Mijozlar bazasini yuritish', 'ru' => 'Ведение базы клиентов'],
                ['uz' => 'Sovuq qo\'ng\'iroqlar', 'ru' => 'Холодные звонки'],
                ['uz' => 'E\'tirozlar bilan ishlash', 'ru' => 'Работа с возражениями'],
                ['uz' => 'IP-telefoniya', 'ru' => 'IP-телефония'],
                ['uz' => 'Kunlik hisobot', 'ru' => 'Ежедневный отчёт'],
                ['uz' => 'Ko\'p vazifalilik', 'ru' => 'Многозадачность'],
            ],

            // Marketing - Sales Manager
            'marketing-sales-manager' => [
                ['uz' => 'Sotuv jarayonini boshqarish', 'ru' => 'Управление процессом продаж'],
                ['uz' => 'B2B va B2C sotuv', 'ru' => 'B2B и B2C продажи'],
                ['uz' => 'Tijorat takliflari tayyorlash', 'ru' => 'Подготовка коммерческих предложений'],
                ['uz' => 'Muzokaralar yuritish', 'ru' => 'Ведение переговоров'],
                ['uz' => 'Sotuv rejasini bajarish', 'ru' => 'Выполнение плана продаж'],
                ['uz' => 'CRM tizimi', 'ru' => 'CRM система'],
                ['uz' => 'Shartnomalar tuzish', 'ru' => 'Составление договоров'],
                ['uz' => 'Mijozlar bilan uzoq muddatli munosabatlar', 'ru' => 'Долгосрочные отношения с клиентами'],
                ['uz' => 'Prezentatsiya ko\'nikmalari', 'ru' => 'Навыки презентации'],
                ['uz' => 'Raqobatchilar tahlili', 'ru' => 'Анализ конкурентов'],
            ],

            // Marketing - Marketologist
            'marketing-marketolog' => [
                ['uz' => 'Marketing tadqiqotlari', 'ru' => 'Маркетинговые исследования'],
                ['uz' => 'Raqamli marketing', 'ru' => 'Цифровой маркетинг'],
                ['uz' => 'Marketing strategiyasini ishlab chiqish', 'ru' => 'Разработка маркетинговой стратегии'],
                ['uz' => 'Bozor segmentatsiyasi', 'ru' => 'Сегментация рынка'],
                ['uz' => 'Marketing byudjeti', 'ru' => 'Маркетинговый бюджет'],
                ['uz' => 'Reklama kampaniyalari', 'ru' => 'Рекламные кампании'],
                ['uz' => 'Google Analytics', 'ru' => 'Google Analytics'],
                ['uz' => 'SWOT tahlil', 'ru' => 'SWOT анализ'],
                ['uz' => 'Marketing KPI', 'ru' => 'Маркетинговые KPI'],
                ['uz' => 'Mijoz sayohatini tahlil qilish (CJM)', 'ru' => 'Анализ пути клиента (CJM)'],
            ],

            // Marketing - Targetologist
            'marketing-targetolog' => [
                ['uz' => 'Facebook/Instagram reklama', 'ru' => 'Реклама Facebook/Instagram'],
                ['uz' => 'Google Ads', 'ru' => 'Google Ads'],
                ['uz' => 'Yandex.Direct', 'ru' => 'Яндекс.Директ'],
                ['uz' => 'Target auditoriyani aniqlash', 'ru' => 'Определение целевой аудитории'],
                ['uz' => 'A/B testlash', 'ru' => 'A/B тестирование'],
                ['uz' => 'Reklama byudjetini boshqarish', 'ru' => 'Управление рекламным бюджетом'],
                ['uz' => 'Retargeting va remarketing', 'ru' => 'Ретаргетинг и ремаркетинг'],
                ['uz' => 'Konversiyani optimallashtirish', 'ru' => 'Оптимизация конверсии'],
                ['uz' => 'Reklama kreativlarini yaratish', 'ru' => 'Создание рекламных креативов'],
                ['uz' => 'Analitika va hisobotlar', 'ru' => 'Аналитика и отчёты'],
                ['uz' => 'Telegram Ads', 'ru' => 'Telegram Ads'],
            ],

            // =============================================
            // 10. Logistics (Parent)
            // =============================================
            'logistics' => [
                ['uz' => 'Logistika asoslari', 'ru' => 'Основы логистики'],
                ['uz' => 'Ombor boshqaruvi', 'ru' => 'Управление складом'],
                ['uz' => 'Yuk tashish', 'ru' => 'Грузоперевозки'],
                ['uz' => 'Hujjat yuritish', 'ru' => 'Документооборот'],
                ['uz' => 'Vaqtni boshqarish', 'ru' => 'Управление временем'],
                ['uz' => 'Jismoniy chidamlilik', 'ru' => 'Физическая выносливость'],
            ],

            // Logistics - Warehouse
            'logistics-warehouse' => [
                ['uz' => 'Tovarlarni qabul qilish va berish', 'ru' => 'Приём и выдача товаров'],
                ['uz' => 'Inventarizatsiya o\'tkazish', 'ru' => 'Проведение инвентаризации'],
                ['uz' => 'Yuk ko\'targich (shtabler) bilan ishlash', 'ru' => 'Работа с погрузчиком (штабелёром)'],
                ['uz' => '1C Ombor', 'ru' => '1С Склад'],
                ['uz' => 'Tovarlarni joylashtirish', 'ru' => 'Размещение товаров'],
                ['uz' => 'Ombor hujjatlari', 'ru' => 'Складская документация'],
                ['uz' => 'Shtrix-kod skaneri', 'ru' => 'Сканер штрих-кодов'],
                ['uz' => 'Tovarlarni saralash', 'ru' => 'Сортировка товаров'],
                ['uz' => 'Saqlash qoidalari', 'ru' => 'Правила хранения'],
                ['uz' => 'WMS tizimi', 'ru' => 'WMS система'],
            ],

            // Logistics - Courier
            'logistics-courier' => [
                ['uz' => 'Shahar bo\'ylab navigatsiya', 'ru' => 'Навигация по городу'],
                ['uz' => 'Buyurtmalarni tezkor yetkazish', 'ru' => 'Быстрая доставка заказов'],
                ['uz' => 'Mijozlar bilan muloqot', 'ru' => 'Общение с клиентами'],
                ['uz' => 'Ilovalar bilan ishlash', 'ru' => 'Работа с приложениями'],
                ['uz' => 'Piyoda yetkazib berish', 'ru' => 'Пешая доставка'],
                ['uz' => 'Naqd pul bilan ishlash', 'ru' => 'Работа с наличными'],
                ['uz' => 'Vaqtga rioya qilish', 'ru' => 'Соблюдение сроков'],
                ['uz' => 'Buyurtmani saqlash va xavfsizligi', 'ru' => 'Сохранность заказа'],
                ['uz' => 'Jismoniy chidamlilik', 'ru' => 'Физическая выносливость'],
                ['uz' => 'Hududni bilish', 'ru' => 'Знание территории'],
            ],

            // Logistics - Loader
            'logistics-loader' => [
                ['uz' => 'Yuk ortish va tushirish', 'ru' => 'Погрузка и разгрузка'],
                ['uz' => 'Jismoniy kuch va chidamlilik', 'ru' => 'Физическая сила и выносливость'],
                ['uz' => 'Yuk xavfsizligini ta\'minlash', 'ru' => 'Обеспечение сохранности груза'],
                ['uz' => 'Jamoaviy ishlash', 'ru' => 'Работа в команде'],
                ['uz' => 'Og\'ir yuklarni ko\'tarish texnikasi', 'ru' => 'Техника подъёма тяжестей'],
                ['uz' => 'Omborda ishlash', 'ru' => 'Работа на складе'],
                ['uz' => 'Mebel ko\'chirish', 'ru' => 'Перевозка мебели'],
                ['uz' => 'Qurilish materiallarini ko\'chirish', 'ru' => 'Перемещение строительных материалов'],
                ['uz' => 'Tartiblilik va intizom', 'ru' => 'Аккуратность и дисциплина'],
                ['uz' => 'Yuk ko\'targich bilan ishlash', 'ru' => 'Работа с грузоподъёмником'],
            ],

            // Logistics - Dispatcher
            'logistics-dispatcher' => [
                ['uz' => 'Logistikani boshqarish', 'ru' => 'Управление логистикой'],
                ['uz' => 'Marshrut rejalashtirish', 'ru' => 'Планирование маршрутов'],
                ['uz' => 'Haydovchilar bilan koordinatsiya', 'ru' => 'Координация с водителями'],
                ['uz' => 'Transport hujjatlari', 'ru' => 'Транспортная документация'],
                ['uz' => 'CRM/ERP tizimlarida ishlash', 'ru' => 'Работа в CRM/ERP системах'],
                ['uz' => 'Buyurtmalarni taqsimlash', 'ru' => 'Распределение заказов'],
                ['uz' => 'GPS monitoring tizimi', 'ru' => 'Система GPS мониторинга'],
                ['uz' => 'Muammolarni tezkor hal qilish', 'ru' => 'Быстрое решение проблем'],
                ['uz' => 'Ko\'p vazifalilik', 'ru' => 'Многозадачность'],
                ['uz' => 'Hisobotlar tayyorlash', 'ru' => 'Подготовка отчётов'],
            ],

            // =============================================
            // 11. Security (Parent)
            // =============================================
            'security' => [
                ['uz' => 'Jismoniy tayyorgarlik', 'ru' => 'Физическая подготовка'],
                ['uz' => 'Xavfsizlik qoidalari', 'ru' => 'Правила безопасности'],
                ['uz' => 'Kuzatuvchanlik', 'ru' => 'Наблюдательность'],
                ['uz' => 'Stress bardoshlilik', 'ru' => 'Стрессоустойчивость'],
                ['uz' => 'Mas\'uliyatlilik', 'ru' => 'Ответственность'],
            ],

            // Security - Guard
            'security-guard' => [
                ['uz' => 'Obyektni qo\'riqlash', 'ru' => 'Охрана объекта'],
                ['uz' => 'Kirish-chiqishni nazorat qilish', 'ru' => 'Контроль доступа'],
                ['uz' => 'Kechki va tungi navbatchilik', 'ru' => 'Вечерние и ночные дежурства'],
                ['uz' => 'Favqulodda vaziyatlarda harakat', 'ru' => 'Действия в чрезвычайных ситуациях'],
                ['uz' => 'Aloqa vositalari bilan ishlash', 'ru' => 'Работа со средствами связи'],
                ['uz' => 'Jismoniy tayyorgarlik', 'ru' => 'Физическая подготовка'],
                ['uz' => 'Birinchi tibbiy yordam', 'ru' => 'Первая медицинская помощь'],
                ['uz' => 'Hisobot yuritish', 'ru' => 'Ведение отчётности'],
                ['uz' => 'O\'t o\'chirish vositalarini bilish', 'ru' => 'Знание средств пожаротушения'],
                ['uz' => 'Qarama-qarshiliklarni bartaraf etish', 'ru' => 'Разрешение конфликтов'],
            ],

            // Security - CCTV
            'security-cctv' => [
                ['uz' => 'Video kuzatuv tizimlari bilan ishlash', 'ru' => 'Работа с системами видеонаблюдения'],
                ['uz' => 'Monitorlarni kuzatish', 'ru' => 'Наблюдение за мониторами'],
                ['uz' => 'Video yozuvlarni qayta ko\'rish', 'ru' => 'Просмотр видеозаписей'],
                ['uz' => 'Kameralarni sozlash', 'ru' => 'Настройка камер'],
                ['uz' => 'Shubhali harakatlarni aniqlash', 'ru' => 'Выявление подозрительных действий'],
                ['uz' => 'Hisobot yozish', 'ru' => 'Составление отчётов'],
                ['uz' => 'IP kameralar bilan ishlash', 'ru' => 'Работа с IP-камерами'],
                ['uz' => 'Kompyuter bilan ishlash', 'ru' => 'Работа с компьютером'],
                ['uz' => 'Tarmoq sozlamalari asoslari', 'ru' => 'Основы сетевых настроек'],
                ['uz' => 'Diqqatlilik va sabr-toqat', 'ru' => 'Внимательность и терпение'],
            ],

            // =============================================
            // 12. Cleaning (Parent)
            // =============================================
            'cleaning' => [
                ['uz' => 'Tozalash vositalari bilimi', 'ru' => 'Знание чистящих средств'],
                ['uz' => 'Tozalik standartlari', 'ru' => 'Стандарты чистоты'],
                ['uz' => 'Tartiblilik', 'ru' => 'Аккуратность'],
                ['uz' => 'Jismoniy chidamlilik', 'ru' => 'Физическая выносливость'],
                ['uz' => 'Tozalash jihozlari bilan ishlash', 'ru' => 'Работа с уборочным оборудованием'],
            ],

            // Cleaning - Office
            'cleaning-office' => [
                ['uz' => 'Ofis xonalarini tozalash', 'ru' => 'Уборка офисных помещений'],
                ['uz' => 'Pol yuvish va artish', 'ru' => 'Мытьё и протирка полов'],
                ['uz' => 'Deraza yuvish', 'ru' => 'Мытьё окон'],
                ['uz' => 'Mebel tozalash', 'ru' => 'Чистка мебели'],
                ['uz' => 'Axlat chiqarish va saralash', 'ru' => 'Вынос и сортировка мусора'],
                ['uz' => 'Hojatxona va oshxonani tozalash', 'ru' => 'Уборка санузлов и кухни'],
                ['uz' => 'Tozalash kimyoviy vositalari', 'ru' => 'Химические чистящие средства'],
                ['uz' => 'Tozalash jadvali bo\'yicha ishlash', 'ru' => 'Работа по графику уборки'],
                ['uz' => 'Carpet tozalash', 'ru' => 'Чистка ковров'],
                ['uz' => 'Dezinfeksiya', 'ru' => 'Дезинфекция'],
            ],

            // Cleaning - Home
            'cleaning-home' => [
                ['uz' => 'Uy-joy tozalash', 'ru' => 'Уборка жилых помещений'],
                ['uz' => 'Kir yuvish va dazmollash', 'ru' => 'Стирка и глажка'],
                ['uz' => 'Ovqat tayyorlash (asosiy)', 'ru' => 'Приготовление еды (базовое)'],
                ['uz' => 'Oshxona tozalash', 'ru' => 'Уборка кухни'],
                ['uz' => 'Vannaxona tozalash', 'ru' => 'Уборка ванной'],
                ['uz' => 'Chang artish va changyutgich', 'ru' => 'Протирка пыли и пылесос'],
                ['uz' => 'O\'simliklarni parvarish qilish', 'ru' => 'Уход за растениями'],
                ['uz' => 'Oziq-ovqat sotib olish', 'ru' => 'Покупка продуктов'],
                ['uz' => 'Uy jihozlari bilan ishlash', 'ru' => 'Работа с бытовой техникой'],
                ['uz' => 'Halollik va ishonchlilik', 'ru' => 'Честность и надёжность'],
            ],

            // Cleaning - Industrial
            'cleaning-industrial' => [
                ['uz' => 'Sanoat binolarini tozalash', 'ru' => 'Уборка промышленных зданий'],
                ['uz' => 'Professional tozalash jihozlari', 'ru' => 'Профессиональное уборочное оборудование'],
                ['uz' => 'Yuqori bosimli yuvish', 'ru' => 'Мойка высоким давлением'],
                ['uz' => 'Kimyoviy tozalash vositalari', 'ru' => 'Химические чистящие средства'],
                ['uz' => 'Xavfsizlik texnikasi', 'ru' => 'Техника безопасности'],
                ['uz' => 'Fasadlarni tozalash', 'ru' => 'Мойка фасадов'],
                ['uz' => 'Pol tozalash mashinalari', 'ru' => 'Поломоечные машины'],
                ['uz' => 'Quruq tozalash', 'ru' => 'Сухая чистка'],
                ['uz' => 'Balandlikda ishlash', 'ru' => 'Работа на высоте'],
                ['uz' => 'Smenali ish jadvali', 'ru' => 'Сменный график работы'],
            ],

            // =============================================
            // 13. Admin (Parent)
            // =============================================
            'admin' => [
                ['uz' => 'Ofis dasturlari (Word, Excel)', 'ru' => 'Офисные программы (Word, Excel)'],
                ['uz' => 'Hujjat yuritish', 'ru' => 'Делопроизводство'],
                ['uz' => 'Telefon muloqoti', 'ru' => 'Телефонные переговоры'],
                ['uz' => 'Tashkiliy qobiliyat', 'ru' => 'Организаторские способности'],
                ['uz' => 'Muloqot madaniyati', 'ru' => 'Культура общения'],
                ['uz' => 'Ko\'p vazifalilik', 'ru' => 'Многозадачность'],
            ],

            // Admin - Reception
            'admin-reception' => [
                ['uz' => 'Mehmonlarni kutib olish', 'ru' => 'Встреча и приём гостей'],
                ['uz' => 'Telefon qo\'ng\'iroqlarni boshqarish', 'ru' => 'Управление телефонными звонками'],
                ['uz' => 'Uchrashuvlarni rejalashtirish', 'ru' => 'Планирование встреч'],
                ['uz' => 'Yoqimli tashqi ko\'rinish', 'ru' => 'Приятный внешний вид'],
                ['uz' => 'CRM tizimida ishlash', 'ru' => 'Работа в CRM системе'],
                ['uz' => 'Xat-xabar yuritish', 'ru' => 'Ведение корреспонденции'],
                ['uz' => 'Ofis jihozlari bilan ishlash', 'ru' => 'Работа с офисной техникой'],
                ['uz' => 'Ko\'p tilli muloqot (o\'zbek/rus)', 'ru' => 'Многоязычное общение (узбекский/русский)'],
                ['uz' => 'Stressga chidamlilik', 'ru' => 'Стрессоустойчивость'],
                ['uz' => 'Mijozlarga yo\'naltirish', 'ru' => 'Клиентоориентированность'],
            ],

            // Admin - Secretary
            'admin-secretary' => [
                ['uz' => 'Hujjatlar tayyorlash', 'ru' => 'Подготовка документов'],
                ['uz' => 'Rahbarning jadvalini boshqarish', 'ru' => 'Управление расписанием руководителя'],
                ['uz' => 'Protokollar yuritish', 'ru' => 'Ведение протоколов'],
                ['uz' => 'Xatlar va yozishmalar', 'ru' => 'Письма и переписка'],
                ['uz' => 'Arxiv bilan ishlash', 'ru' => 'Работа с архивом'],
                ['uz' => 'Tezkor matn terish', 'ru' => 'Быстрый набор текста'],
                ['uz' => 'Ofis dasturlari (Word, Excel, PowerPoint)', 'ru' => 'Офисные программы (Word, Excel, PowerPoint)'],
                ['uz' => 'Maxfiylikni saqlash', 'ru' => 'Соблюдение конфиденциальности'],
                ['uz' => 'Xizmat safarlarini tashkil qilish', 'ru' => 'Организация командировок'],
                ['uz' => 'Elektron pochta bilan ishlash', 'ru' => 'Работа с электронной почтой'],
            ],

            // Admin - HR
            'admin-hr' => [
                ['uz' => 'Xodimlarni tanlash va yollash', 'ru' => 'Подбор и наём персонала'],
                ['uz' => 'Suhbat o\'tkazish', 'ru' => 'Проведение собеседований'],
                ['uz' => 'Mehnat qonunchiligi bilimi', 'ru' => 'Знание трудового законодательства'],
                ['uz' => 'Kadrlar hujjatlari', 'ru' => 'Кадровая документация'],
                ['uz' => 'Onboarding jarayoni', 'ru' => 'Процесс онбординга'],
                ['uz' => 'Xodimlar bazasini yuritish', 'ru' => 'Ведение базы сотрудников'],
                ['uz' => 'Mehnat shartnomalarini tuzish', 'ru' => 'Составление трудовых договоров'],
                ['uz' => 'HeadHunter/OLX/Telegram bilan ishlash', 'ru' => 'Работа с HeadHunter/OLX/Telegram'],
                ['uz' => 'HR analitika', 'ru' => 'HR аналитика'],
                ['uz' => '1C Kadrlar', 'ru' => '1С Кадры'],
            ],

            // Admin - Manager
            'admin-manager' => [
                ['uz' => 'Ofis ishlarini tashkil qilish', 'ru' => 'Организация офисных дел'],
                ['uz' => 'Xo\'jalik jihozlarini ta\'minlash', 'ru' => 'Обеспечение хозяйственных нужд'],
                ['uz' => 'Yetkazib beruvchilar bilan ish', 'ru' => 'Работа с поставщиками'],
                ['uz' => 'Byudjetni nazorat qilish', 'ru' => 'Контроль бюджета'],
                ['uz' => 'Tadbirlarni tashkil qilish', 'ru' => 'Организация мероприятий'],
                ['uz' => 'Xodimlar uchun sharoit yaratish', 'ru' => 'Создание условий для сотрудников'],
                ['uz' => 'Shartnomalar bilan ishlash', 'ru' => 'Работа с договорами'],
                ['uz' => 'Ofis jihozlari sotib olish', 'ru' => 'Закупка офисного оборудования'],
                ['uz' => 'Kommunal xizmatlar bilan ish', 'ru' => 'Работа с коммунальными службами'],
                ['uz' => 'Hisobotlar tayyorlash', 'ru' => 'Подготовка отчётов'],
            ],

            // Admin - Call Center
            'admin-callcenter' => [
                ['uz' => 'Telefon orqali muloqot', 'ru' => 'Телефонные переговоры'],
                ['uz' => 'CRM tizimida ishlash', 'ru' => 'Работа в CRM системе'],
                ['uz' => 'Mijoz shikoyatlarini hal qilish', 'ru' => 'Решение жалоб клиентов'],
                ['uz' => 'Skript bo\'yicha ishlash', 'ru' => 'Работа по скрипту'],
                ['uz' => 'Tez va aniq javob berish', 'ru' => 'Быстрые и точные ответы'],
                ['uz' => 'Ko\'p liniyali telefon tizimi', 'ru' => 'Многоканальная телефонная система'],
                ['uz' => 'Ma\'lumotlarni kiritish', 'ru' => 'Ввод данных'],
                ['uz' => 'Stressga chidamlilik', 'ru' => 'Стрессоустойчивость'],
                ['uz' => 'Mijoz so\'rovlarini qayta ishlash', 'ru' => 'Обработка запросов клиентов'],
                ['uz' => 'IP-telefoniya va chat botlar', 'ru' => 'IP-телефония и чат-боты'],
            ],

            // =============================================
            // 14. Production (Parent)
            // =============================================
            'production' => [
                ['uz' => 'Ishlab chiqarish jarayonlari', 'ru' => 'Производственные процессы'],
                ['uz' => 'Xavfsizlik texnikasi', 'ru' => 'Техника безопасности'],
                ['uz' => 'Sifat nazorati', 'ru' => 'Контроль качества'],
                ['uz' => 'Jihozlar bilan ishlash', 'ru' => 'Работа с оборудованием'],
                ['uz' => 'Jamoaviy ish', 'ru' => 'Командная работа'],
                ['uz' => 'Smenali ish jadvali', 'ru' => 'Сменный график работы'],
            ],

            // Production - Operator
            'production-operator' => [
                ['uz' => 'Stanoklar bilan ishlash (tokarlik, frezerlash)', 'ru' => 'Работа со станками (токарный, фрезерный)'],
                ['uz' => 'CNC stanoklar', 'ru' => 'Станки с ЧПУ'],
                ['uz' => 'Texnik chizmalarni o\'qish', 'ru' => 'Чтение технических чертежей'],
                ['uz' => 'O\'lchov asboblari bilan ishlash', 'ru' => 'Работа с измерительными приборами'],
                ['uz' => 'Metallni qayta ishlash', 'ru' => 'Обработка металла'],
                ['uz' => 'Jihozni sozlash', 'ru' => 'Настройка оборудования'],
                ['uz' => 'Sifat nazorati', 'ru' => 'Контроль качества'],
                ['uz' => 'Xavfsizlik qoidalari', 'ru' => 'Правила безопасности'],
                ['uz' => 'Profilaktik texnik xizmat', 'ru' => 'Профилактическое техобслуживание'],
                ['uz' => 'Ishlab chiqarish rejasi bo\'yicha ishlash', 'ru' => 'Работа по производственному плану'],
            ],

            // Production - Sewing
            'production-sewing' => [
                ['uz' => 'Tikuv mashinasida ishlash', 'ru' => 'Работа на швейной машине'],
                ['uz' => 'Overlok bilan ishlash', 'ru' => 'Работа на оверлоке'],
                ['uz' => 'Andoza (lekalo) bo\'yicha kesish', 'ru' => 'Раскрой по лекалам'],
                ['uz' => 'Turli matolar bilan ishlash', 'ru' => 'Работа с разными тканями'],
                ['uz' => 'Kiyim tikish', 'ru' => 'Пошив одежды'],
                ['uz' => 'Kashta tikish (gulduzi)', 'ru' => 'Вышивка'],
                ['uz' => 'Pardoz va bezak', 'ru' => 'Отделка и декор'],
                ['uz' => 'Sifat nazorati', 'ru' => 'Контроль качества'],
                ['uz' => 'Tez va sifatli tikish', 'ru' => 'Быстрый и качественный пошив'],
                ['uz' => 'Avtomatik tikuv mashinalari', 'ru' => 'Автоматические швейные машины'],
                ['uz' => 'Buyurtmachi talablariga mos ishlash', 'ru' => 'Работа по требованиям заказчика'],
                ['uz' => 'Milliy kiyimlar tikish', 'ru' => 'Пошив национальной одежды'],
            ],

            // Production - Packer
            'production-packer' => [
                ['uz' => 'Mahsulotni qadoqlash', 'ru' => 'Упаковка продукции'],
                ['uz' => 'Qadoqlash jihozlari bilan ishlash', 'ru' => 'Работа с упаковочным оборудованием'],
                ['uz' => 'Etiketka yopishtirish', 'ru' => 'Наклейка этикеток'],
                ['uz' => 'Sifat tekshiruvi', 'ru' => 'Проверка качества'],
                ['uz' => 'Tayyor mahsulotni saralash', 'ru' => 'Сортировка готовой продукции'],
                ['uz' => 'Tez va aniq ishlash', 'ru' => 'Быстрая и точная работа'],
                ['uz' => 'Tartiblilik va tozalik', 'ru' => 'Аккуратность и чистота'],
                ['uz' => 'Konveyer liniyasida ishlash', 'ru' => 'Работа на конвейерной линии'],
                ['uz' => 'Maxsulot sonini hisoblash', 'ru' => 'Подсчёт количества продукции'],
                ['uz' => 'Sanitariya normalari', 'ru' => 'Санитарные нормы'],
            ],

            // Production - Technologist
            'production-technologist' => [
                ['uz' => 'Texnologik jarayonlarni boshqarish', 'ru' => 'Управление технологическими процессами'],
                ['uz' => 'Sifat standartlari (GOST, ISO)', 'ru' => 'Стандарты качества (ГОСТ, ISO)'],
                ['uz' => 'Texnik hujjatlar ishlab chiqish', 'ru' => 'Разработка технической документации'],
                ['uz' => 'Xom ashyo sifatini nazorat qilish', 'ru' => 'Контроль качества сырья'],
                ['uz' => 'Ishlab chiqarish retsepturalari', 'ru' => 'Производственные рецептуры'],
                ['uz' => 'Laboratoriya tahlillari', 'ru' => 'Лабораторные анализы'],
                ['uz' => 'Jihozlarni tanlash va sozlash', 'ru' => 'Выбор и настройка оборудования'],
                ['uz' => 'Texnologik xaritalar tuzish', 'ru' => 'Составление технологических карт'],
                ['uz' => 'Xavfsizlik va ekologiya', 'ru' => 'Безопасность и экология'],
                ['uz' => 'Ishlab chiqarish samaradorligini oshirish', 'ru' => 'Повышение эффективности производства'],
            ],

            // =============================================
            // 15. Other (Parent)
            // =============================================
            'other' => [
                ['uz' => 'Moslashuvchanlik', 'ru' => 'Гибкость'],
                ['uz' => 'Mustaqil ishlash', 'ru' => 'Самостоятельная работа'],
                ['uz' => 'Vaqtni boshqarish', 'ru' => 'Управление временем'],
                ['uz' => 'Muloqot ko\'nikmalari', 'ru' => 'Коммуникативные навыки'],
                ['uz' => 'Tez o\'rganish qobiliyati', 'ru' => 'Быстрая обучаемость'],
            ],

            // Other - Freelance
            'other-freelance' => [
                ['uz' => 'Vaqtni mustaqil boshqarish', 'ru' => 'Самостоятельное управление временем'],
                ['uz' => 'Masofaviy ishlash tajribasi', 'ru' => 'Опыт удалённой работы'],
                ['uz' => 'Loyihalarni boshqarish', 'ru' => 'Управление проектами'],
                ['uz' => 'Mijozlar bilan muloqot', 'ru' => 'Общение с клиентами'],
                ['uz' => 'Portfolio yaratish', 'ru' => 'Создание портфолио'],
                ['uz' => 'Freelance platformalar (Upwork, Fiverr)', 'ru' => 'Freelance платформы (Upwork, Fiverr)'],
                ['uz' => 'Shartnoma va to\'lov masalalari', 'ru' => 'Вопросы договоров и оплаты'],
                ['uz' => 'Deadline bo\'yicha ishlash', 'ru' => 'Работа по дедлайнам'],
                ['uz' => 'O\'z-o\'zini motivatsiya', 'ru' => 'Самомотивация'],
                ['uz' => 'Ko\'p loyihali ish', 'ru' => 'Работа с несколькими проектами'],
            ],

            // Other - Part-time
            'other-parttime' => [
                ['uz' => 'Moslashuvchan jadval', 'ru' => 'Гибкий график'],
                ['uz' => 'Tez o\'rganish', 'ru' => 'Быстрая обучаемость'],
                ['uz' => 'Turli sohalarda tajriba', 'ru' => 'Опыт в разных сферах'],
                ['uz' => 'Mas\'uliyatlilik', 'ru' => 'Ответственность'],
                ['uz' => 'Jismoniy chidamlilik', 'ru' => 'Физическая выносливость'],
                ['uz' => 'Jamoaviy ish', 'ru' => 'Командная работа'],
                ['uz' => 'Punktuallik', 'ru' => 'Пунктуальность'],
                ['uz' => 'Vaqtinchalik loyihalar', 'ru' => 'Временные проекты'],
                ['uz' => 'Ko\'p vazifalilik', 'ru' => 'Многозадачность'],
                ['uz' => 'Asosiy kompyuter ko\'nikmalari', 'ru' => 'Базовые компьютерные навыки'],
            ],

            // Other - Internship
            'other-internship' => [
                ['uz' => 'O\'rganishga tayyor', 'ru' => 'Готовность к обучению'],
                ['uz' => 'Asosiy kompyuter savodxonligi', 'ru' => 'Базовая компьютерная грамотность'],
                ['uz' => 'Jamoaviy ishlash', 'ru' => 'Работа в команде'],
                ['uz' => 'Muloqot ko\'nikmalari', 'ru' => 'Коммуникативные навыки'],
                ['uz' => 'Tashabbuskorlik', 'ru' => 'Инициативность'],
                ['uz' => 'Vaqtni boshqarish', 'ru' => 'Управление временем'],
                ['uz' => 'Mas\'uliyatlilik', 'ru' => 'Ответственность'],
                ['uz' => 'Analitik fikrlash', 'ru' => 'Аналитическое мышление'],
                ['uz' => 'Yangi bilimlarni tez o\'zlashtirish', 'ru' => 'Быстрое усвоение новых знаний'],
                ['uz' => 'Kasbiy maqsadlar', 'ru' => 'Профессиональные цели'],
            ],
        ];

        foreach ($skills as $slug => $categorySkills) {
            Category::where('slug', $slug)->update([
                'default_skills' => json_encode($categorySkills, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }
}
