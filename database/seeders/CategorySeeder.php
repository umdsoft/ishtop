<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'it', 'name_uz' => 'IT va dasturlash', 'name_ru' => 'IT и программирование',
                'icon' => 'heroicon-o-computer-desktop', 'emoji' => '💻', 'sort_order' => 1,
                'children' => [
                    ['slug' => 'it-frontend', 'name_uz' => 'Frontend dasturchi', 'name_ru' => 'Frontend разработчик', 'sort_order' => 1],
                    ['slug' => 'it-backend', 'name_uz' => 'Backend dasturchi', 'name_ru' => 'Backend разработчик', 'sort_order' => 2],
                    ['slug' => 'it-mobile', 'name_uz' => 'Mobil dasturchi', 'name_ru' => 'Мобильный разработчик', 'sort_order' => 3],
                    ['slug' => 'it-designer', 'name_uz' => 'UI/UX dizayner', 'name_ru' => 'UI/UX дизайнер', 'sort_order' => 4],
                    ['slug' => 'it-devops', 'name_uz' => 'DevOps muhandis', 'name_ru' => 'DevOps инженер', 'sort_order' => 5],
                    ['slug' => 'it-qa', 'name_uz' => 'QA/Tester', 'name_ru' => 'QA/Тестировщик', 'sort_order' => 6],
                    ['slug' => 'it-data', 'name_uz' => 'Data analitik', 'name_ru' => 'Дата-аналитик', 'sort_order' => 7],
                    ['slug' => 'it-ai', 'name_uz' => 'AI/ML mutaxassisi', 'name_ru' => 'AI/ML специалист', 'sort_order' => 8],
                    ['slug' => 'it-other', 'name_uz' => 'Boshqa IT', 'name_ru' => 'Другое IT', 'sort_order' => 9],
                ],
            ],
            [
                'slug' => 'sales', 'name_uz' => 'Sotuvchi', 'name_ru' => 'Продавец',
                'icon' => 'heroicon-o-shopping-bag', 'emoji' => '🛒', 'sort_order' => 2,
                'children' => [
                    ['slug' => 'sales-shop', 'name_uz' => 'Do\'kon sotuvchisi', 'name_ru' => 'Продавец в магазине', 'sort_order' => 1],
                    ['slug' => 'sales-market', 'name_uz' => 'Bozor sotuvchisi', 'name_ru' => 'Продавец на рынке', 'sort_order' => 2],
                    ['slug' => 'sales-consultant', 'name_uz' => 'Sotish bo\'yicha maslahatchi', 'name_ru' => 'Консультант по продажам', 'sort_order' => 3],
                    ['slug' => 'sales-cashier', 'name_uz' => 'Kassir', 'name_ru' => 'Кассир', 'sort_order' => 4],
                    ['slug' => 'sales-wholesale', 'name_uz' => 'Ulgurji savdo', 'name_ru' => 'Оптовая торговля', 'sort_order' => 5],
                    ['slug' => 'sales-marketplace', 'name_uz' => 'Marketplace menejeri', 'name_ru' => 'Менеджер маркетплейса', 'sort_order' => 6],
                    ['slug' => 'sales-telesales', 'name_uz' => 'Telesotuvchi', 'name_ru' => 'Телепродавец', 'sort_order' => 7],
                    ['slug' => 'sales-b2b', 'name_uz' => 'B2B sotuv menejeri', 'name_ru' => 'B2B менеджер по продажам', 'sort_order' => 8],
                    ['slug' => 'sales-crm', 'name_uz' => 'CRM menejeri', 'name_ru' => 'CRM менеджер', 'sort_order' => 9],
                ],
            ],
            [
                'slug' => 'food', 'name_uz' => 'Oshxona va restoran', 'name_ru' => 'Кухня и ресторан',
                'icon' => 'heroicon-o-cake', 'emoji' => '🍽', 'sort_order' => 3,
                'children' => [
                    ['slug' => 'food-cook', 'name_uz' => 'Oshpaz', 'name_ru' => 'Повар', 'sort_order' => 1],
                    ['slug' => 'food-waiter', 'name_uz' => 'Ofitsiant', 'name_ru' => 'Официант', 'sort_order' => 2],
                    ['slug' => 'food-barista', 'name_uz' => 'Barista', 'name_ru' => 'Бариста', 'sort_order' => 3],
                    ['slug' => 'food-baker', 'name_uz' => 'Novvoy', 'name_ru' => 'Пекарь', 'sort_order' => 4],
                    ['slug' => 'food-dishwasher', 'name_uz' => 'Idish yuvuvchi', 'name_ru' => 'Посудомойщик', 'sort_order' => 5],
                ],
            ],
            [
                'slug' => 'driver', 'name_uz' => 'Haydovchi', 'name_ru' => 'Водитель',
                'icon' => 'heroicon-o-truck', 'emoji' => '🚗', 'sort_order' => 4,
                'children' => [
                    ['slug' => 'driver-taxi', 'name_uz' => 'Taksi haydovchisi', 'name_ru' => 'Водитель такси', 'sort_order' => 1],
                    ['slug' => 'driver-truck', 'name_uz' => 'Yuk mashinasi haydovchisi', 'name_ru' => 'Водитель грузовика', 'sort_order' => 2],
                    ['slug' => 'driver-delivery', 'name_uz' => 'Yetkazib beruvchi (kuryer)', 'name_ru' => 'Курьер-водитель', 'sort_order' => 3],
                    ['slug' => 'driver-personal', 'name_uz' => 'Shaxsiy haydovchi', 'name_ru' => 'Личный водитель', 'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'construction', 'name_uz' => 'Qurilish va ta\'mirlash', 'name_ru' => 'Строительство и ремонт',
                'icon' => 'heroicon-o-wrench-screwdriver', 'emoji' => '🔧', 'sort_order' => 5,
                'children' => [
                    ['slug' => 'construction-builder', 'name_uz' => 'Qurilishchi', 'name_ru' => 'Строитель', 'sort_order' => 1],
                    ['slug' => 'construction-electrician', 'name_uz' => 'Elektrchi', 'name_ru' => 'Электрик', 'sort_order' => 2],
                    ['slug' => 'construction-plumber', 'name_uz' => 'Santexnik', 'name_ru' => 'Сантехник', 'sort_order' => 3],
                    ['slug' => 'construction-painter', 'name_uz' => 'Bo\'yoqchi', 'name_ru' => 'Маляр', 'sort_order' => 4],
                    ['slug' => 'construction-welder', 'name_uz' => 'Payvandchi', 'name_ru' => 'Сварщик', 'sort_order' => 5],
                    ['slug' => 'construction-carpenter', 'name_uz' => 'Duradgor', 'name_ru' => 'Плотник', 'sort_order' => 6],
                ],
            ],
            [
                'slug' => 'beauty', 'name_uz' => 'Go\'zallik va sog\'liq', 'name_ru' => 'Красота и здоровье',
                'icon' => 'heroicon-o-heart', 'emoji' => '💇', 'sort_order' => 6,
                'children' => [
                    ['slug' => 'beauty-hairdresser', 'name_uz' => 'Sartarosh', 'name_ru' => 'Парикмахер', 'sort_order' => 1],
                    ['slug' => 'beauty-manicure', 'name_uz' => 'Manikurchi', 'name_ru' => 'Мастер маникюра', 'sort_order' => 2],
                    ['slug' => 'beauty-cosmetologist', 'name_uz' => 'Kosmetolog', 'name_ru' => 'Косметолог', 'sort_order' => 3],
                    ['slug' => 'beauty-masseur', 'name_uz' => 'Massajchi', 'name_ru' => 'Массажист', 'sort_order' => 4],
                    ['slug' => 'beauty-nurse', 'name_uz' => 'Hamshira', 'name_ru' => 'Медсестра', 'sort_order' => 5],
                    ['slug' => 'beauty-pharmacist', 'name_uz' => 'Farmatsevt', 'name_ru' => 'Фармацевт', 'sort_order' => 6],
                    ['slug' => 'beauty-makeup', 'name_uz' => 'Vizajist', 'name_ru' => 'Визажист', 'sort_order' => 7],
                    ['slug' => 'beauty-brow', 'name_uz' => 'Brovist', 'name_ru' => 'Бровист', 'sort_order' => 8],
                    ['slug' => 'beauty-lash', 'name_uz' => 'Kirpik ustasi (Lashmaker)', 'name_ru' => 'Лэшмейкер', 'sort_order' => 9],
                ],
            ],
            [
                'slug' => 'education', 'name_uz' => 'Ta\'lim va o\'qituvchi', 'name_ru' => 'Образование и преподавание',
                'icon' => 'heroicon-o-academic-cap', 'emoji' => '🎓', 'sort_order' => 7,
                'children' => [
                    ['slug' => 'education-teacher', 'name_uz' => 'O\'qituvchi', 'name_ru' => 'Учитель', 'sort_order' => 1],
                    ['slug' => 'education-tutor', 'name_uz' => 'Repetitor', 'name_ru' => 'Репетитор', 'sort_order' => 2],
                    ['slug' => 'education-trainer', 'name_uz' => 'Trener/Murabbiy', 'name_ru' => 'Тренер', 'sort_order' => 3],
                    ['slug' => 'education-nanny', 'name_uz' => 'Enaga/Tarbiyachi', 'name_ru' => 'Няня/Воспитатель', 'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'finance', 'name_uz' => 'Moliya va buxgalteriya', 'name_ru' => 'Финансы и бухгалтерия',
                'icon' => 'heroicon-o-banknotes', 'emoji' => '💰', 'sort_order' => 8,
                'children' => [
                    ['slug' => 'finance-accountant', 'name_uz' => 'Buxgalter', 'name_ru' => 'Бухгалтер', 'sort_order' => 1],
                    ['slug' => 'finance-economist', 'name_uz' => 'Iqtisodchi', 'name_ru' => 'Экономист', 'sort_order' => 2],
                    ['slug' => 'finance-banker', 'name_uz' => 'Bank xodimi', 'name_ru' => 'Банковский работник', 'sort_order' => 3],
                    ['slug' => 'finance-auditor', 'name_uz' => 'Auditor', 'name_ru' => 'Аудитор', 'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'marketing', 'name_uz' => 'Marketing va reklama', 'name_ru' => 'Маркетинг и реклама',
                'icon' => 'heroicon-o-megaphone', 'emoji' => '📢', 'sort_order' => 9,
                'children' => [
                    ['slug' => 'marketing-smm', 'name_uz' => 'SMM mutaxassisi', 'name_ru' => 'SMM специалист', 'sort_order' => 1],
                    ['slug' => 'marketing-content', 'name_uz' => 'Kontent menejeri', 'name_ru' => 'Контент менеджер', 'sort_order' => 2],
                    ['slug' => 'marketing-seo', 'name_uz' => 'SEO mutaxassisi', 'name_ru' => 'SEO специалист', 'sort_order' => 3],
                    ['slug' => 'marketing-designer', 'name_uz' => 'Grafik dizayner', 'name_ru' => 'Графический дизайнер', 'sort_order' => 4],
                    ['slug' => 'marketing-pr', 'name_uz' => 'PR mutaxassisi', 'name_ru' => 'PR специалист', 'sort_order' => 5],
                    ['slug' => 'marketing-sales-operator', 'name_uz' => 'Sotuv operatori', 'name_ru' => 'Оператор продаж', 'sort_order' => 6],
                    ['slug' => 'marketing-sales-manager', 'name_uz' => 'Sotuv menejeri', 'name_ru' => 'Менеджер по продажам', 'sort_order' => 7],
                    ['slug' => 'marketing-marketolog', 'name_uz' => 'Marketolog', 'name_ru' => 'Маркетолог', 'sort_order' => 8],
                    ['slug' => 'marketing-targetolog', 'name_uz' => 'Targetolog', 'name_ru' => 'Таргетолог', 'sort_order' => 9],
                    ['slug' => 'marketing-mobilograf', 'name_uz' => 'Mobilograf', 'name_ru' => 'Мобилограф', 'sort_order' => 10],
                    ['slug' => 'marketing-copywriter', 'name_uz' => 'Kopyrayiter', 'name_ru' => 'Копирайтер', 'sort_order' => 11],
                    ['slug' => 'marketing-video', 'name_uz' => 'Video montajchi', 'name_ru' => 'Видеомонтажёр', 'sort_order' => 12],
                    ['slug' => 'marketing-context', 'name_uz' => 'Kontekst reklama mutaxassisi', 'name_ru' => 'Специалист по контекстной рекламе', 'sort_order' => 13],
                    ['slug' => 'marketing-reels', 'name_uz' => 'Reels/TikTok kontentchi', 'name_ru' => 'Создатель Reels/TikTok', 'sort_order' => 14],
                    ['slug' => 'marketing-brandface', 'name_uz' => 'Brend yuz (Brand face)', 'name_ru' => 'Лицо бренда (Brand face)', 'sort_order' => 15],
                    ['slug' => 'marketing-photographer', 'name_uz' => 'Fotograf', 'name_ru' => 'Фотограф', 'sort_order' => 16],
                    ['slug' => 'marketing-blogger-manager', 'name_uz' => 'Bloger/Influenser menejeri', 'name_ru' => 'Менеджер блогеров/инфлюенсеров', 'sort_order' => 17],
                    ['slug' => 'marketing-brand-manager', 'name_uz' => 'Brend menejeri', 'name_ru' => 'Бренд менеджер', 'sort_order' => 18],
                ],
            ],
            [
                'slug' => 'logistics', 'name_uz' => 'Logistika va ombor', 'name_ru' => 'Логистика и склад',
                'icon' => 'heroicon-o-cube', 'emoji' => '📦', 'sort_order' => 10,
                'children' => [
                    ['slug' => 'logistics-warehouse', 'name_uz' => 'Omborchi', 'name_ru' => 'Кладовщик', 'sort_order' => 1],
                    ['slug' => 'logistics-courier', 'name_uz' => 'Kuryer', 'name_ru' => 'Курьер', 'sort_order' => 2],
                    ['slug' => 'logistics-loader', 'name_uz' => 'Yukchi', 'name_ru' => 'Грузчик', 'sort_order' => 3],
                    ['slug' => 'logistics-dispatcher', 'name_uz' => 'Dispetcher', 'name_ru' => 'Диспетчер', 'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'security', 'name_uz' => 'Qo\'riqlash', 'name_ru' => 'Охрана',
                'icon' => 'heroicon-o-shield-check', 'emoji' => '🛡', 'sort_order' => 11,
                'children' => [
                    ['slug' => 'security-guard', 'name_uz' => 'Qo\'riqchi', 'name_ru' => 'Охранник', 'sort_order' => 1],
                    ['slug' => 'security-cctv', 'name_uz' => 'Kuzatuv operatori', 'name_ru' => 'Оператор видеонаблюдения', 'sort_order' => 2],
                ],
            ],
            [
                'slug' => 'cleaning', 'name_uz' => 'Tozalash xizmati', 'name_ru' => 'Клининг',
                'icon' => 'heroicon-o-sparkles', 'emoji' => '✨', 'sort_order' => 12,
                'children' => [
                    ['slug' => 'cleaning-office', 'name_uz' => 'Ofis tozalagich', 'name_ru' => 'Уборщик офиса', 'sort_order' => 1],
                    ['slug' => 'cleaning-home', 'name_uz' => 'Uy tozalagich', 'name_ru' => 'Домработница', 'sort_order' => 2],
                    ['slug' => 'cleaning-industrial', 'name_uz' => 'Sanoat tozalash', 'name_ru' => 'Промышленная уборка', 'sort_order' => 3],
                ],
            ],
            [
                'slug' => 'admin', 'name_uz' => 'Ofis va administrator', 'name_ru' => 'Офис и администратор',
                'icon' => 'heroicon-o-building-office', 'emoji' => '🏢', 'sort_order' => 13,
                'children' => [
                    ['slug' => 'admin-reception', 'name_uz' => 'Resepshnchi', 'name_ru' => 'Ресепшионист', 'sort_order' => 1],
                    ['slug' => 'admin-secretary', 'name_uz' => 'Kotiba', 'name_ru' => 'Секретарь', 'sort_order' => 2],
                    ['slug' => 'admin-hr', 'name_uz' => 'HR mutaxassisi', 'name_ru' => 'HR специалист', 'sort_order' => 3],
                    ['slug' => 'admin-manager', 'name_uz' => 'Ofis menejeri', 'name_ru' => 'Офис менеджер', 'sort_order' => 4],
                    ['slug' => 'admin-callcenter', 'name_uz' => 'Call-center operatori', 'name_ru' => 'Оператор call-центра', 'sort_order' => 5],
                ],
            ],
            [
                'slug' => 'production', 'name_uz' => 'Ishlab chiqarish', 'name_ru' => 'Производство',
                'icon' => 'heroicon-o-cog-6-tooth', 'emoji' => '⚙️', 'sort_order' => 14,
                'children' => [
                    ['slug' => 'production-operator', 'name_uz' => 'Stanok operatori', 'name_ru' => 'Оператор станка', 'sort_order' => 1],
                    ['slug' => 'production-sewing', 'name_uz' => 'Tikuvchi', 'name_ru' => 'Швея', 'sort_order' => 2],
                    ['slug' => 'production-packer', 'name_uz' => 'Qadoqchi', 'name_ru' => 'Упаковщик', 'sort_order' => 3],
                    ['slug' => 'production-technologist', 'name_uz' => 'Texnolog', 'name_ru' => 'Технолог', 'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'other', 'name_uz' => 'Boshqa', 'name_ru' => 'Другое',
                'icon' => 'heroicon-o-ellipsis-horizontal-circle', 'emoji' => '📋', 'sort_order' => 15,
                'children' => [
                    ['slug' => 'other-freelance', 'name_uz' => 'Frilanser', 'name_ru' => 'Фрилансер', 'sort_order' => 1],
                    ['slug' => 'other-parttime', 'name_uz' => 'Yarim stavka', 'name_ru' => 'Подработка', 'sort_order' => 2],
                    ['slug' => 'other-internship', 'name_uz' => 'Stajirovka', 'name_ru' => 'Стажировка', 'sort_order' => 3],
                ],
            ],
        ];

        foreach ($categories as $catData) {
            $children = $catData['children'] ?? [];
            unset($catData['children']);

            $parent = Category::updateOrCreate(['slug' => $catData['slug']], $catData);

            foreach ($children as $child) {
                $child['parent_id'] = $parent->id;
                $child['icon'] = $catData['icon'];
                $child['emoji'] = $catData['emoji'];
                $child['is_active'] = true;
                Category::updateOrCreate(['slug' => $child['slug']], $child);
            }
        }
    }
}
