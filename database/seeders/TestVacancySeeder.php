<?php

namespace Database\Seeders;

use App\Models\EmployerProfile;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Seeder;

class TestVacancySeeder extends Seeder
{
    public function run(): void
    {
        $employers = $this->createEmployers();
        $this->createVacancies($employers);

        $this->command->info('12 ta test vakansiya muvaffaqiyatli yaratildi!');
    }

    private function createEmployers(): array
    {
        $data = [
            [
                'company_name' => 'TechUz Solutions',
                'industry' => 'it',
                'description' => 'O\'zbekistondagi yetakchi IT kompaniya. Web va mobil ilovalar ishlab chiqish.',
                'phone' => '+998901234567',
                'website' => 'https://techuz.uz',
                'employees_count' => '51-200',
                'verification_level' => 'verified',
                'rating' => 4.7,
                'rating_count' => 23,
                'city' => 'Toshkent',
                'latitude' => 41.3111,
                'longitude' => 69.2797,
            ],
            [
                'company_name' => 'Samarqand Grand Hotel',
                'industry' => 'food',
                'description' => 'Samarqand shahridagi 5 yulduzli mehmonxona va restoran tarmog\'i.',
                'phone' => '+998662331122',
                'website' => 'https://samarkand-grand.uz',
                'employees_count' => '201-500',
                'verification_level' => 'verified',
                'rating' => 4.5,
                'rating_count' => 15,
                'city' => 'Samarqand',
                'latitude' => 39.6542,
                'longitude' => 66.9597,
            ],
            [
                'company_name' => 'Farg\'ona Tekstil',
                'industry' => 'production',
                'description' => 'Farg\'ona vodiysidagi yirik to\'qimachilik korxonasi. Eksport yo\'nalishi.',
                'phone' => '+998731234567',
                'website' => null,
                'employees_count' => '500+',
                'verification_level' => 'confirmed',
                'rating' => 4.1,
                'rating_count' => 8,
                'city' => 'Farg\'ona',
                'latitude' => 40.3842,
                'longitude' => 71.7889,
            ],
            [
                'company_name' => 'Buxoro Savdo Markazi',
                'industry' => 'retail',
                'description' => 'Buxoro shahridagi eng yirik savdo markazi. 200+ do\'kon.',
                'phone' => '+998651112233',
                'website' => null,
                'employees_count' => '51-200',
                'verification_level' => 'confirmed',
                'rating' => 3.9,
                'rating_count' => 12,
                'city' => 'Buxoro',
                'latitude' => 39.7747,
                'longitude' => 64.4286,
            ],
            [
                'company_name' => 'Nukus Logistika',
                'industry' => 'logistics',
                'description' => 'Qoraqalpog\'iston bo\'ylab yuk tashish va ombor xizmatlari.',
                'phone' => '+998612345678',
                'website' => null,
                'employees_count' => '11-50',
                'verification_level' => 'new',
                'rating' => 0,
                'rating_count' => 0,
                'city' => 'Nukus',
                'latitude' => 42.4628,
                'longitude' => 59.6003,
            ],
            [
                'company_name' => 'Namangan Building',
                'industry' => 'construction',
                'description' => 'Namangan viloyatidagi qurilish va loyihalash kompaniyasi.',
                'phone' => '+998692223344',
                'website' => null,
                'employees_count' => '51-200',
                'verification_level' => 'confirmed',
                'rating' => 4.3,
                'rating_count' => 6,
                'city' => 'Namangan',
                'latitude' => 40.9983,
                'longitude' => 71.6726,
            ],
            [
                'company_name' => 'Digital Marketing Pro',
                'industry' => 'marketing',
                'description' => 'Raqamli marketing agentligi. SMM, SEO, targetlangan reklama.',
                'phone' => '+998909876543',
                'website' => 'https://dmpro.uz',
                'employees_count' => '11-50',
                'verification_level' => 'verified',
                'rating' => 4.8,
                'rating_count' => 31,
                'city' => 'Toshkent',
                'latitude' => 41.2995,
                'longitude' => 69.2401,
            ],
            [
                'company_name' => 'Qarshi Medical Center',
                'industry' => 'medicine',
                'description' => 'Qashqadaryo viloyatidagi zamonaviy tibbiyot markazi.',
                'phone' => '+998752221133',
                'website' => null,
                'employees_count' => '51-200',
                'verification_level' => 'verified',
                'rating' => 4.6,
                'rating_count' => 19,
                'city' => 'Qarshi',
                'latitude' => 38.8600,
                'longitude' => 65.7983,
            ],
        ];

        $employers = [];

        foreach ($data as $i => $emp) {
            $user = User::create([
                'telegram_id' => 900000 + $i,
                'first_name' => 'Employer' . ($i + 1),
                'last_name' => 'Test',
                'username' => 'test_employer_' . ($i + 1),
                'language' => 'uz',
                'is_verified' => true,
                'is_blocked' => false,
                'balance' => 500000,
                'last_active_at' => now(),
            ]);

            $profile = EmployerProfile::create([
                'user_id' => $user->id,
                'company_name' => $emp['company_name'],
                'industry' => $emp['industry'],
                'description' => $emp['description'],
                'phone' => $emp['phone'],
                'website' => $emp['website'],
                'employees_count' => $emp['employees_count'],
                'verification_level' => $emp['verification_level'],
                'rating' => $emp['rating'],
                'rating_count' => $emp['rating_count'],
                'latitude' => $emp['latitude'],
                'longitude' => $emp['longitude'],
            ]);

            $user->update(['active_employer_id' => $profile->id]);

            $employers[$emp['company_name']] = $profile;
        }

        return $employers;
    }

    private function createVacancies(array $employers): void
    {
        $vacancies = [
            // 1. Toshkent — IT — Senior Laravel dasturchi (TOP)
            [
                'employer' => 'TechUz Solutions',
                'title_uz' => 'Senior Laravel dasturchi',
                'title_ru' => 'Senior Laravel разработчик',
                'category' => 'it',
                'description_uz' => "TechUz Solutions jamoasiga tajribali Laravel dasturchi kerak.\n\nBiz O'zbekiston bozori uchun SaaS platformalar ishlab chiqamiz. Jamoamizda 15+ dasturchi ishlaydi.\n\nNima qilamiz:\n- E-commerce platformalar\n- CRM va ERP tizimlar\n- API integratsiyalar\n\nBiz taklif qilamiz:\n- Zamonaviy texnologiyalar (Laravel 11, Vue 3, Docker)\n- Remote ishlash imkoniyati\n- Professional o'sish",
                'description_ru' => "В команду TechUz Solutions требуется опытный Laravel разработчик.\n\nМы разрабатываем SaaS платформы для рынка Узбекистана. В нашей команде 15+ разработчиков.\n\nНаши проекты:\n- E-commerce платформы\n- CRM и ERP системы\n- API интеграции",
                'requirements_uz' => "- Laravel 3+ yil tajriba\n- MySQL, Redis, Queue bilimi\n- REST API loyihalash tajribasi\n- Git, CI/CD\n- Ingliz tili (texnik hujjatlar darajasida)",
                'requirements_ru' => "- Опыт работы с Laravel 3+ года\n- Знание MySQL, Redis, Queue\n- Опыт проектирования REST API\n- Git, CI/CD\n- Английский язык (чтение документации)",
                'responsibilities_uz' => "- Backend tizimlarni loyihalash va ishlab chiqish\n- Kod tekshirish (code review)\n- Junior dasturchilarga mentorlik\n- Arxitektura qarorlarida ishtirok",
                'salary_min' => 12000000,
                'salary_max' => 20000000,
                'salary_type' => 'range',
                'work_type' => 'full_time',
                'experience_required' => '3-5',
                'city' => 'Toshkent',
                'district' => 'Yunusobod',
                'latitude' => 41.3111,
                'longitude' => 69.2797,
                'contact_phone' => '+998901234567',
                'is_top' => true,
                'is_urgent' => false,
                'views_count' => 342,
                'applications_count' => 18,
            ],

            // 2. Samarqand — Oshxona — Bosh oshpaz (URGENT)
            [
                'employer' => 'Samarqand Grand Hotel',
                'title_uz' => 'Bosh oshpaz (European & O\'zbek taomlari)',
                'title_ru' => 'Шеф-повар (европейская и узбекская кухня)',
                'category' => 'food',
                'description_uz' => "Samarqand Grand Hotel restoraniga bosh oshpaz talab qilinadi.\n\n5 yulduzli mehmonxonamizda kuniga 200+ mehmon xizmat ko'rsatiladi. Restoranimiz milliy va yevropa oshxonasini birlashtiradi.\n\nMehmonxona shartnoma asosida bepul turar-joy taqdim etadi.",
                'description_ru' => "В ресторан Samarkand Grand Hotel требуется шеф-повар.\n\nВ нашем 5-звездочном отеле ежедневно обслуживается 200+ гостей. Ресторан сочетает национальную и европейскую кухню.",
                'requirements_uz' => "- Oshpazlik bo'yicha 5+ yil tajriba\n- Yevropa va O'zbek taomlari bilimi\n- Gigiena sertifikati\n- Jamoa boshqarish tajribasi\n- Kreativ yondashuv",
                'salary_min' => 8000000,
                'salary_max' => 15000000,
                'salary_type' => 'range',
                'work_type' => 'full_time',
                'experience_required' => '5+',
                'city' => 'Samarqand',
                'district' => 'Registon',
                'latitude' => 39.6542,
                'longitude' => 66.9597,
                'contact_phone' => '+998662331122',
                'is_top' => false,
                'is_urgent' => true,
                'views_count' => 187,
                'applications_count' => 7,
            ],

            // 3. Farg'ona — Ishlab chiqarish — Tikuvchi
            [
                'employer' => 'Farg\'ona Tekstil',
                'title_uz' => 'Tikuvchi (ayollar kiyimi tsekhi)',
                'title_ru' => 'Швея (цех женской одежды)',
                'category' => 'production',
                'description_uz' => "Farg'ona Tekstil korxonasiga tikuvchilar kerak.\n\nBiz Yevropa eksporti uchun ayollar kiyimlarini tikamiz. Zamonaviy Yaponiya stanoklari bilan jihozlangan tsex.\n\nAfzalliklar:\n- Kunlik tushlik bepul\n- Transport xizmati\n- Oylik bonus tizimi",
                'description_ru' => "На предприятие Фергана Текстиль требуются швеи.\n\nМы шьём женскую одежду на экспорт в Европу. Цех оснащён современными японскими станками.",
                'requirements_uz' => "- Tikuvchilik tajribasi 1+ yil\n- Sanoat tikuv mashinalarida ishlash\n- Mas'uliyatlilik va tezkorlik",
                'salary_min' => 3500000,
                'salary_max' => 7000000,
                'salary_type' => 'range',
                'work_type' => 'full_time',
                'experience_required' => '1-3',
                'city' => 'Farg\'ona',
                'district' => 'Sanoat zonasi',
                'latitude' => 40.3842,
                'longitude' => 71.7889,
                'contact_phone' => '+998731234567',
                'is_top' => false,
                'is_urgent' => false,
                'views_count' => 95,
                'applications_count' => 12,
            ],

            // 4. Buxoro — Sotuvchi — Do'kon menejeri
            [
                'employer' => 'Buxoro Savdo Markazi',
                'title_uz' => 'Do\'kon menejeri (elektronika bo\'limi)',
                'title_ru' => 'Менеджер магазина (отдел электроники)',
                'category' => 'sales',
                'description_uz' => "Buxoro Savdo Markazidagi elektronika do'koniga menejer kerak.\n\nSiz 5-6 kishilik jamoani boshqarasiz. Do'konning kundalik savdo hajmi 50+ mln so'm.\n\nIsh grafigi: 09:00 - 18:00, yakshanba dam olish.",
                'description_ru' => "В магазин электроники Бухарского Торгового Центра требуется менеджер.\n\nВы будете управлять командой из 5-6 человек. Ежедневный оборот магазина 50+ млн сум.",
                'requirements_uz' => "- Savdo sohasida 2+ yil tajriba\n- Jamoa boshqarish ko'nikmalari\n- Kompyuter savodxonligi\n- O'zbek va rus tillarida muloqot",
                'salary_min' => 5000000,
                'salary_max' => 8000000,
                'salary_type' => 'range',
                'work_type' => 'full_time',
                'experience_required' => '1-3',
                'city' => 'Buxoro',
                'district' => 'Markaz',
                'latitude' => 39.7747,
                'longitude' => 64.4286,
                'contact_phone' => '+998651112233',
                'is_top' => false,
                'is_urgent' => false,
                'views_count' => 128,
                'applications_count' => 9,
            ],

            // 5. Nukus — Logistika — Yuk mashina haydovchisi
            [
                'employer' => 'Nukus Logistika',
                'title_uz' => 'Yuk mashina haydovchisi (shaharlararo)',
                'title_ru' => 'Водитель грузовика (междугородний)',
                'category' => 'driver',
                'description_uz' => "Nukus Logistika kompaniyasiga shaharlararo yuk tashish uchun haydovchi kerak.\n\nYo'nalishlar: Nukus — Toshkent, Nukus — Buxoro, Nukus — Urganch.\n\nMashina kompaniya tomonidan beriladi (MAN TGX yoki DAF).\nYoqilg'i va ovqatlanish xarajatlari kompaniya hisobidan.",
                'description_ru' => "В компанию Нукус Логистика требуется водитель грузовика для междугородних перевозок.\n\nМаршруты: Нукус — Ташкент, Нукус — Бухара, Нукус — Ургенч.",
                'requirements_uz' => "- BC toifali haydovchilik guvohnomasi\n- Yuk tashish tajribasi 3+ yil\n- Uzoq masofaga haydash tajribasi\n- Jinoyat yozuvi yo'qligi",
                'salary_min' => 6000000,
                'salary_max' => 10000000,
                'salary_type' => 'range',
                'work_type' => 'full_time',
                'experience_required' => '3-5',
                'city' => 'Nukus',
                'district' => null,
                'latitude' => 42.4628,
                'longitude' => 59.6003,
                'contact_phone' => '+998612345678',
                'is_top' => false,
                'is_urgent' => true,
                'views_count' => 64,
                'applications_count' => 3,
            ],

            // 6. Namangan — Qurilish — Prораб/Qurilish boshqaruvchisi (TOP)
            [
                'employer' => 'Namangan Building',
                'title_uz' => 'Qurilish boshqaruvchisi (Prorab)',
                'title_ru' => 'Прораб (руководитель строительства)',
                'category' => 'construction',
                'description_uz' => "Namangan shahridagi ko'p qavatli turar-joy qurilishiga prorab kerak.\n\n16 qavatli 3 ta bino qurilishi. Loyiha muddati 18 oy.\n\nJamoada 50+ ishchi va 5 muhandis ishlaydi.",
                'description_ru' => "На строительство многоэтажного жилого комплекса в Намангане требуется прораб.\n\n3 здания по 16 этажей. Срок проекта 18 месяцев.",
                'requirements_uz' => "- Qurilish sohasida 5+ yil tajriba\n- Ko'p qavatli bino qurilishi tajribasi\n- Qurilish me'yorlari bilimi (SNiP)\n- Jamoa boshqarish ko'nikmalari\n- AutoCAD yoki loyiha o'qish",
                'salary_min' => 10000000,
                'salary_max' => 18000000,
                'salary_type' => 'range',
                'work_type' => 'full_time',
                'experience_required' => '5+',
                'city' => 'Namangan',
                'district' => 'Yangi Namangan',
                'latitude' => 40.9983,
                'longitude' => 71.6726,
                'contact_phone' => '+998692223344',
                'is_top' => true,
                'is_urgent' => false,
                'views_count' => 215,
                'applications_count' => 11,
            ],

            // 7. Toshkent — Marketing — SMM mutaxassisi (remote)
            [
                'employer' => 'Digital Marketing Pro',
                'title_uz' => 'SMM mutaxassisi (Instagram + Telegram)',
                'title_ru' => 'SMM специалист (Instagram + Telegram)',
                'category' => 'marketing',
                'description_uz' => "Digital Marketing Pro agentligiga SMM mutaxassisi kerak.\n\nSiz 5-8 ta brendning ijtimoiy tarmoqlarini boshqarasiz.\n\nAfzalliklar:\n- Remote ishlash (ofisga kelish shart emas)\n- Moslashuvchan ish grafigi\n- Har oylik treninglar va masterklasslar\n- Natijaga qarab bonus",
                'description_ru' => "В агентство Digital Marketing Pro требуется SMM специалист.\n\nВы будете вести социальные сети 5-8 брендов.\n\nПреимущества:\n- Удалённая работа\n- Гибкий график\n- Ежемесячные тренинги",
                'requirements_uz' => "- SMM bo'yicha 1+ yil tajriba\n- Instagram Reels va Telegram kanallarni boshqarish\n- Canva yoki Figma da kontentlar tayyorlash\n- Yozma va og'zaki kreativlik\n- Portfolio taqdim etish shart",
                'salary_min' => 4000000,
                'salary_max' => 8000000,
                'salary_type' => 'range',
                'work_type' => 'remote',
                'experience_required' => '1-3',
                'city' => 'Toshkent',
                'district' => null,
                'latitude' => null,
                'longitude' => null,
                'contact_phone' => '+998909876543',
                'is_top' => false,
                'is_urgent' => false,
                'views_count' => 456,
                'applications_count' => 34,
            ],

            // 8. Qarshi — Tibbiyot — Hamshira
            [
                'employer' => 'Qarshi Medical Center',
                'title_uz' => 'Oliy toifali hamshira',
                'title_ru' => 'Медсестра высшей категории',
                'category' => 'beauty',
                'description_uz' => "Qarshi Medical Center zamonaviy tibbiyot markaziga hamshira kerak.\n\nIsh joyi: terapiya bo'limi.\n\nBiz taklif qilamiz:\n- Zamonaviy asbob-uskunalar\n- Malaka oshirish imkoniyati\n- Tibbiy sug'urta\n- Barqaror ish joyi",
                'description_ru' => "В Qarshi Medical Center требуется медсестра высшей категории.\n\nМесто работы: терапевтическое отделение.\n\nМы предлагаем:\n- Современное оборудование\n- Повышение квалификации\n- Медицинская страховка",
                'requirements_uz' => "- Oliy yoki o'rta tibbiy ma'lumot\n- Hamshiralik tajribasi 2+ yil\n- Tibbiy asboblar bilan ishlash\n- Mas'uliyatlilik va g'amxo'rlik",
                'salary_min' => 3000000,
                'salary_max' => 5000000,
                'salary_type' => 'range',
                'work_type' => 'full_time',
                'experience_required' => '1-3',
                'city' => 'Qarshi',
                'district' => 'Markaz',
                'latitude' => 38.8600,
                'longitude' => 65.7983,
                'contact_phone' => '+998752221133',
                'is_top' => false,
                'is_urgent' => false,
                'views_count' => 73,
                'applications_count' => 5,
            ],

            // 9. Toshkent — IT — Flutter dasturchi (TOP + URGENT)
            [
                'employer' => 'TechUz Solutions',
                'title_uz' => 'Middle Flutter dasturchi',
                'title_ru' => 'Middle Flutter разработчик',
                'category' => 'it',
                'description_uz' => "TechUz Solutions jamoasiga Flutter dasturchi kerak.\n\nBiz fintech va e-commerce ilovalar ishlab chiqamiz. Hozirda 3 ta aktiv mobil loyiha bor.\n\nStek: Flutter, Dart, Firebase, REST API, BLoC pattern.",
                'description_ru' => "В команду TechUz Solutions требуется Flutter разработчик.\n\nМы разрабатываем fintech и e-commerce приложения. Сейчас 3 активных мобильных проекта.",
                'requirements_uz' => "- Flutter 2+ yil tajriba\n- State management (BLoC/Riverpod)\n- REST API integratsiya\n- iOS va Android publish tajribasi\n- Git bilimi",
                'salary_min' => 8000000,
                'salary_max' => 15000000,
                'salary_type' => 'range',
                'work_type' => 'full_time',
                'experience_required' => '1-3',
                'city' => 'Toshkent',
                'district' => 'Mirzo Ulug\'bek',
                'latitude' => 41.3389,
                'longitude' => 69.3350,
                'contact_phone' => '+998901234567',
                'is_top' => true,
                'is_urgent' => true,
                'views_count' => 523,
                'applications_count' => 27,
            ],

            // 10. Farg'ona — Ta'lim — Ingliz tili o'qituvchisi
            [
                'employer' => 'Farg\'ona Tekstil',
                'title_uz' => 'Ingliz tili o\'qituvchisi (korporativ)',
                'title_ru' => 'Преподаватель английского (корпоративный)',
                'category' => 'education',
                'description_uz' => "Farg'ona Tekstil korxonasi xodimlari uchun ingliz tili o'qituvchisi kerak.\n\nDarslar korxona hududida o'tkaziladi. Haftada 3 kun, har kuni 2 soat.\n\nMaqsad: xodimlarning ingliz tili bilimini B1 darajasiga yetkazish (chet ellik hamkorlar bilan muloqot uchun).",
                'description_ru' => "Предприятию Фергана Текстиль требуется преподаватель английского языка для сотрудников.\n\nЗанятия на территории предприятия. 3 дня в неделю по 2 часа.\n\nЦель: довести знание английского до B1.",
                'requirements_uz' => "- Ingliz tili CEFR B2+ darajada\n- O'qituvchilik tajribasi 1+ yil\n- Kattalar bilan ishlash tajribasi\n- IELTS/CEFR sertifikat afzallik",
                'salary_min' => 3000000,
                'salary_max' => 5000000,
                'salary_type' => 'range',
                'work_type' => 'part_time',
                'experience_required' => '1-3',
                'city' => 'Farg\'ona',
                'district' => 'Sanoat zonasi',
                'latitude' => 40.3842,
                'longitude' => 71.7889,
                'contact_phone' => '+998731234567',
                'is_top' => false,
                'is_urgent' => false,
                'views_count' => 112,
                'applications_count' => 8,
            ],

            // 11. Buxoro — Qo'riqlash — Tungi navbatchi
            [
                'employer' => 'Buxoro Savdo Markazi',
                'title_uz' => 'Qo\'riqchi (tungi smena)',
                'title_ru' => 'Охранник (ночная смена)',
                'category' => 'security',
                'description_uz' => "Buxoro Savdo Markaziga tungi smenada ishlaydigan qo'riqchi kerak.\n\nIsh grafigi: 20:00 - 08:00, 2/2 navbat.\n\nMajburiyatlar:\n- Savdo markazi hududini kuzatish\n- Kamera monitoringini nazorat qilish\n- Favqulodda holatlarda tezkor choralar ko'rish",
                'description_ru' => "В Бухарский Торговый Центр требуется охранник на ночную смену.\n\nГрафик: 20:00 - 08:00, 2/2.",
                'requirements_uz' => "- Jismoniy sog'lom, 22-50 yosh\n- Jinoyat yozuvi yo'qligi\n- Harbiy xizmatdan o'tgan\n- Mas'uliyatlilik",
                'salary_min' => 2500000,
                'salary_max' => 3500000,
                'salary_type' => 'range',
                'work_type' => 'full_time',
                'experience_required' => 'no_experience',
                'city' => 'Buxoro',
                'district' => 'Markaz',
                'latitude' => 39.7747,
                'longitude' => 64.4286,
                'contact_phone' => '+998651112233',
                'is_top' => false,
                'is_urgent' => false,
                'views_count' => 45,
                'applications_count' => 4,
            ],

            // 12. Toshkent — Moliya — Buxgalter
            [
                'employer' => 'Digital Marketing Pro',
                'title_uz' => 'Buxgalter (1C va soliq hisoboti)',
                'title_ru' => 'Бухгалтер (1С и налоговая отчётность)',
                'category' => 'finance',
                'description_uz' => "Digital Marketing Pro agentligiga buxgalter kerak.\n\nSiz kompaniya moliyaviy hisobotlarini yuritasiz va soliq deklaratsiyalarini tayyorlaysiz.\n\nIsh grafigi: 09:00 - 18:00, shanba-yakshanba dam olish.\nOfis: Toshkent, Chilonzor tumani.",
                'description_ru' => "В агентство Digital Marketing Pro требуется бухгалтер.\n\nВедение финансовой отчётности и подготовка налоговых деклараций.",
                'requirements_uz' => "- Buxgalterlik tajribasi 2+ yil\n- 1C dasturini bilish\n- Soliq qonunchiligi bilimi\n- Excel yaxshi bilish\n- BSI tizimida ishlash tajribasi",
                'salary_min' => 5000000,
                'salary_max' => 8000000,
                'salary_type' => 'range',
                'work_type' => 'full_time',
                'experience_required' => '1-3',
                'city' => 'Toshkent',
                'district' => 'Chilonzor',
                'latitude' => 41.2630,
                'longitude' => 69.2086,
                'contact_phone' => '+998909876543',
                'is_top' => false,
                'is_urgent' => false,
                'views_count' => 167,
                'applications_count' => 14,
            ],
        ];

        foreach ($vacancies as $v) {
            $employerName = $v['employer'];
            unset($v['employer']);

            $defaults = [
                'employer_id' => $employers[$employerName]->id,
                'responsibilities_uz' => null,
                'responsibilities_ru' => null,
                'contact_method' => 'telegram',
                'status' => 'active',
                'published_at' => now()->subDays(rand(1, 14)),
                'expires_at' => now()->addDays(rand(7, 30)),
                'top_until' => $v['is_top'] ? now()->addDays(7) : null,
                'urgent_until' => $v['is_urgent'] ? now()->addDays(3) : null,
                'has_questionnaire' => false,
            ];

            Vacancy::create(array_merge($defaults, $v));
        }
    }
}
