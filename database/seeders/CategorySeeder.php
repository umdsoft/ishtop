<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'it', 'name_uz' => 'IT va dasturlash', 'name_ru' => 'IT и программирование', 'icon' => 'heroicon-o-computer-desktop', 'sort_order' => 1],
            ['slug' => 'sales', 'name_uz' => 'Sotuvchi', 'name_ru' => 'Продавец', 'icon' => 'heroicon-o-shopping-bag', 'sort_order' => 2],
            ['slug' => 'food', 'name_uz' => 'Oshxona va restoran', 'name_ru' => 'Кухня и ресторан', 'icon' => 'heroicon-o-cake', 'sort_order' => 3],
            ['slug' => 'driver', 'name_uz' => 'Haydovchi', 'name_ru' => 'Водитель', 'icon' => 'heroicon-o-truck', 'sort_order' => 4],
            ['slug' => 'construction', 'name_uz' => 'Qurilish va ta\'mirlash', 'name_ru' => 'Строительство и ремонт', 'icon' => 'heroicon-o-wrench-screwdriver', 'sort_order' => 5],
            ['slug' => 'beauty', 'name_uz' => 'Go\'zallik va sog\'liq', 'name_ru' => 'Красота и здоровье', 'icon' => 'heroicon-o-heart', 'sort_order' => 6],
            ['slug' => 'education', 'name_uz' => 'Ta\'lim va o\'qituvchi', 'name_ru' => 'Образование и преподавание', 'icon' => 'heroicon-o-academic-cap', 'sort_order' => 7],
            ['slug' => 'finance', 'name_uz' => 'Moliya va buxgalteriya', 'name_ru' => 'Финансы и бухгалтерия', 'icon' => 'heroicon-o-banknotes', 'sort_order' => 8],
            ['slug' => 'marketing', 'name_uz' => 'Marketing va reklama', 'name_ru' => 'Маркетинг и реклама', 'icon' => 'heroicon-o-megaphone', 'sort_order' => 9],
            ['slug' => 'logistics', 'name_uz' => 'Logistika va ombor', 'name_ru' => 'Логистика и склад', 'icon' => 'heroicon-o-cube', 'sort_order' => 10],
            ['slug' => 'security', 'name_uz' => 'Qo\'riqlash', 'name_ru' => 'Охрана', 'icon' => 'heroicon-o-shield-check', 'sort_order' => 11],
            ['slug' => 'cleaning', 'name_uz' => 'Tozalash xizmati', 'name_ru' => 'Клининг', 'icon' => 'heroicon-o-sparkles', 'sort_order' => 12],
            ['slug' => 'admin', 'name_uz' => 'Ofis va administrator', 'name_ru' => 'Офис и администратор', 'icon' => 'heroicon-o-building-office', 'sort_order' => 13],
            ['slug' => 'production', 'name_uz' => 'Ishlab chiqarish', 'name_ru' => 'Производство', 'icon' => 'heroicon-o-cog-6-tooth', 'sort_order' => 14],
            ['slug' => 'other', 'name_uz' => 'Boshqa', 'name_ru' => 'Другое', 'icon' => 'heroicon-o-ellipsis-horizontal-circle', 'sort_order' => 15],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
