<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        City::query()->delete();

        $cities = [
            // Toshkent shahri
            ['name_uz' => 'Toshkent', 'name_ru' => 'Ташкент', 'region' => 'Toshkent', 'type' => 'shahar', 'latitude' => 41.2995, 'longitude' => 69.2401],

            // Toshkent viloyati
            ['name_uz' => 'Nurafshon', 'name_ru' => 'Нурафшан', 'region' => 'Toshkent viloyati', 'type' => 'shahar', 'latitude' => 41.0645, 'longitude' => 69.3226],
            ['name_uz' => 'Olmaliq', 'name_ru' => 'Алмалык', 'region' => 'Toshkent viloyati', 'type' => 'shahar', 'latitude' => 40.8500, 'longitude' => 69.6000],
            ['name_uz' => 'Angren', 'name_ru' => 'Ангрен', 'region' => 'Toshkent viloyati', 'type' => 'shahar', 'latitude' => 41.0167, 'longitude' => 70.1333],
            ['name_uz' => 'Chirchiq', 'name_ru' => 'Чирчик', 'region' => 'Toshkent viloyati', 'type' => 'shahar', 'latitude' => 41.4689, 'longitude' => 69.5822],
            ['name_uz' => 'Bekobod', 'name_ru' => 'Бекабад', 'region' => 'Toshkent viloyati', 'type' => 'shahar', 'latitude' => 40.2214, 'longitude' => 69.2694],
            ['name_uz' => 'Ohangaron', 'name_ru' => 'Ахангаран', 'region' => 'Toshkent viloyati', 'type' => 'shahar', 'latitude' => 41.0667, 'longitude' => 69.6333],
            ['name_uz' => 'Yangiyo\'l', 'name_ru' => 'Янгиюль', 'region' => 'Toshkent viloyati', 'type' => 'shahar', 'latitude' => 41.1111, 'longitude' => 69.0458],
            ['name_uz' => 'Bo\'ka', 'name_ru' => 'Бука', 'region' => 'Toshkent viloyati', 'type' => 'tuman', 'latitude' => 40.8333, 'longitude' => 69.1833],
            ['name_uz' => 'Bo\'stonliq', 'name_ru' => 'Бостанлык', 'region' => 'Toshkent viloyati', 'type' => 'tuman', 'latitude' => 41.5833, 'longitude' => 69.9500],
            ['name_uz' => 'Chinoz', 'name_ru' => 'Чиназ', 'region' => 'Toshkent viloyati', 'type' => 'tuman', 'latitude' => 40.9333, 'longitude' => 68.7500],
            ['name_uz' => 'Qibray', 'name_ru' => 'Кибрай', 'region' => 'Toshkent viloyati', 'type' => 'tuman', 'latitude' => 41.3833, 'longitude' => 69.4667],
            ['name_uz' => 'Parkent', 'name_ru' => 'Паркент', 'region' => 'Toshkent viloyati', 'type' => 'tuman', 'latitude' => 41.2950, 'longitude' => 69.6747],
            ['name_uz' => 'O\'rtachirchiq', 'name_ru' => 'Уртачирчик', 'region' => 'Toshkent viloyati', 'type' => 'tuman', 'latitude' => 41.1833, 'longitude' => 69.3167],
            ['name_uz' => 'Zangiota', 'name_ru' => 'Зангиата', 'region' => 'Toshkent viloyati', 'type' => 'tuman', 'latitude' => 41.2167, 'longitude' => 69.1000],
            ['name_uz' => 'Piskent', 'name_ru' => 'Пскент', 'region' => 'Toshkent viloyati', 'type' => 'tuman', 'latitude' => 41.0000, 'longitude' => 69.3500],
            ['name_uz' => 'Toshkent', 'name_ru' => 'Ташкент', 'region' => 'Toshkent viloyati', 'type' => 'tuman', 'latitude' => 41.2500, 'longitude' => 69.1667],
            ['name_uz' => 'Quyi chirchiq', 'name_ru' => 'Куйичирчик', 'region' => 'Toshkent viloyati', 'type' => 'tuman', 'latitude' => 41.1500, 'longitude' => 69.0833],
            ['name_uz' => 'Yuqorichirchiq', 'name_ru' => 'Юкоричирчик', 'region' => 'Toshkent viloyati', 'type' => 'tuman', 'latitude' => 41.5167, 'longitude' => 69.5833],

            // Andijon viloyati
            ['name_uz' => 'Andijon', 'name_ru' => 'Андижан', 'region' => 'Andijon', 'type' => 'shahar', 'latitude' => 40.7833, 'longitude' => 72.3333],
            ['name_uz' => 'Xonobod', 'name_ru' => 'Ханабад', 'region' => 'Andijon', 'type' => 'shahar', 'latitude' => 40.6833, 'longitude' => 72.2833],
            ['name_uz' => 'Andijon', 'name_ru' => 'Андижан', 'region' => 'Andijon', 'type' => 'tuman', 'latitude' => 40.8000, 'longitude' => 72.3500],
            ['name_uz' => 'Asaka', 'name_ru' => 'Асака', 'region' => 'Andijon', 'type' => 'tuman', 'latitude' => 40.6333, 'longitude' => 72.2333],
            ['name_uz' => 'Baliqchi', 'name_ru' => 'Балыкчи', 'region' => 'Andijon', 'type' => 'tuman', 'latitude' => 40.8833, 'longitude' => 72.3500],
            ['name_uz' => 'Bo\'z', 'name_ru' => 'Боз', 'region' => 'Andijon', 'type' => 'tuman', 'latitude' => 40.7000, 'longitude' => 72.1500],
            ['name_uz' => 'Buloqboshi', 'name_ru' => 'Булакбаши', 'region' => 'Andijon', 'type' => 'tuman', 'latitude' => 40.6167, 'longitude' => 72.3833],
            ['name_uz' => 'Izboskan', 'name_ru' => 'Избаскан', 'region' => 'Andijon', 'type' => 'tuman', 'latitude' => 40.9167, 'longitude' => 72.2167],
            ['name_uz' => 'Jalaquduq', 'name_ru' => 'Джалакудук', 'region' => 'Andijon', 'type' => 'tuman', 'latitude' => 40.9833, 'longitude' => 72.1333],
            ['name_uz' => 'Marhamat', 'name_ru' => 'Мархамат', 'region' => 'Andijon', 'type' => 'tuman', 'latitude' => 40.7167, 'longitude' => 72.3167],
            ['name_uz' => 'Oltinko\'l', 'name_ru' => 'Алтынкуль', 'region' => 'Andijon', 'type' => 'tuman', 'latitude' => 40.7167, 'longitude' => 72.2500],
            ['name_uz' => 'Paxtaobod', 'name_ru' => 'Пахтаабад', 'region' => 'Andijon', 'type' => 'tuman', 'latitude' => 40.7333, 'longitude' => 72.4333],
            ['name_uz' => 'Qo\'rg\'ontepa', 'name_ru' => 'Кургантепа', 'region' => 'Andijon', 'type' => 'tuman', 'latitude' => 40.8500, 'longitude' => 72.2500],
            ['name_uz' => 'Shahrixon', 'name_ru' => 'Шахрихан', 'region' => 'Andijon', 'type' => 'tuman', 'latitude' => 40.7167, 'longitude' => 72.0500],
            ['name_uz' => 'Ulug\'nor', 'name_ru' => 'Улугнор', 'region' => 'Andijon', 'type' => 'tuman', 'latitude' => 40.8333, 'longitude' => 72.1500],
            ['name_uz' => 'Xo\'jaobod', 'name_ru' => 'Ходжаабад', 'region' => 'Andijon', 'type' => 'tuman', 'latitude' => 40.6333, 'longitude' => 72.4167],

            // Buxoro viloyati
            ['name_uz' => 'Buxoro', 'name_ru' => 'Бухара', 'region' => 'Buxoro', 'type' => 'shahar', 'latitude' => 39.7747, 'longitude' => 64.4286],
            ['name_uz' => 'Kogon', 'name_ru' => 'Каган', 'region' => 'Buxoro', 'type' => 'shahar', 'latitude' => 39.7167, 'longitude' => 64.5500],
            ['name_uz' => 'Buxoro', 'name_ru' => 'Бухара', 'region' => 'Buxoro', 'type' => 'tuman', 'latitude' => 39.8000, 'longitude' => 64.4500],
            ['name_uz' => 'G\'ijduvon', 'name_ru' => 'Гиждуван', 'region' => 'Buxoro', 'type' => 'tuman', 'latitude' => 40.1000, 'longitude' => 64.6833],
            ['name_uz' => 'Jondor', 'name_ru' => 'Жондор', 'region' => 'Buxoro', 'type' => 'tuman', 'latitude' => 39.7167, 'longitude' => 64.1500],
            ['name_uz' => 'Kogon', 'name_ru' => 'Каган', 'region' => 'Buxoro', 'type' => 'tuman', 'latitude' => 39.7333, 'longitude' => 64.5667],
            ['name_uz' => 'Olot', 'name_ru' => 'Алат', 'region' => 'Buxoro', 'type' => 'tuman', 'latitude' => 39.5333, 'longitude' => 64.2167],
            ['name_uz' => 'Peshku', 'name_ru' => 'Пешку', 'region' => 'Buxoro', 'type' => 'tuman', 'latitude' => 39.1333, 'longitude' => 64.1667],
            ['name_uz' => 'Qorako\'l', 'name_ru' => 'Каракуль', 'region' => 'Buxoro', 'type' => 'tuman', 'latitude' => 39.5000, 'longitude' => 63.8500],
            ['name_uz' => 'Qorovulbozor', 'name_ru' => 'Караулбазар', 'region' => 'Buxoro', 'type' => 'tuman', 'latitude' => 39.5000, 'longitude' => 64.7833],
            ['name_uz' => 'Romitan', 'name_ru' => 'Ромитан', 'region' => 'Buxoro', 'type' => 'tuman', 'latitude' => 39.9333, 'longitude' => 64.3833],
            ['name_uz' => 'Shofirkon', 'name_ru' => 'Шафиркан', 'region' => 'Buxoro', 'type' => 'tuman', 'latitude' => 40.1167, 'longitude' => 64.5000],
            ['name_uz' => 'Vobkent', 'name_ru' => 'Вабкент', 'region' => 'Buxoro', 'type' => 'tuman', 'latitude' => 40.0167, 'longitude' => 64.5167],

            // Farg'ona viloyati
            ['name_uz' => 'Farg\'ona', 'name_ru' => 'Фергана', 'region' => 'Farg\'ona', 'type' => 'shahar', 'latitude' => 40.3842, 'longitude' => 71.7889],
            ['name_uz' => 'Marg\'ilon', 'name_ru' => 'Маргилан', 'region' => 'Farg\'ona', 'type' => 'shahar', 'latitude' => 40.4667, 'longitude' => 71.7167],
            ['name_uz' => 'Quvasoy', 'name_ru' => 'Кувасай', 'region' => 'Farg\'ona', 'type' => 'shahar', 'latitude' => 40.5333, 'longitude' => 71.9333],
            ['name_uz' => 'Qo\'qon', 'name_ru' => 'Коканд', 'region' => 'Farg\'ona', 'type' => 'shahar', 'latitude' => 40.5333, 'longitude' => 70.9333],
            ['name_uz' => 'Beshariq', 'name_ru' => 'Бешарык', 'region' => 'Farg\'ona', 'type' => 'tuman', 'latitude' => 40.5000, 'longitude' => 70.7833],
            ['name_uz' => 'Bog\'dod', 'name_ru' => 'Багдад', 'region' => 'Farg\'ona', 'type' => 'tuman', 'latitude' => 40.4500, 'longitude' => 70.8167],
            ['name_uz' => 'Dang\'ara', 'name_ru' => 'Дангара', 'region' => 'Farg\'ona', 'type' => 'tuman', 'latitude' => 40.5833, 'longitude' => 70.6333],
            ['name_uz' => 'Farg\'ona', 'name_ru' => 'Фергана', 'region' => 'Farg\'ona', 'type' => 'tuman', 'latitude' => 40.4000, 'longitude' => 71.8000],
            ['name_uz' => 'Furqat', 'name_ru' => 'Фуркат', 'region' => 'Farg\'ona', 'type' => 'tuman', 'latitude' => 40.5167, 'longitude' => 71.4167],
            ['name_uz' => 'O\'zbekiston', 'name_ru' => 'Узбекистан', 'region' => 'Farg\'ona', 'type' => 'tuman', 'latitude' => 40.6000, 'longitude' => 71.5667],
            ['name_uz' => 'Oltiariq', 'name_ru' => 'Алтыарык', 'region' => 'Farg\'ona', 'type' => 'tuman', 'latitude' => 40.4333, 'longitude' => 71.5833],
            ['name_uz' => 'Quva', 'name_ru' => 'Кува', 'region' => 'Farg\'ona', 'type' => 'tuman', 'latitude' => 40.5167, 'longitude' => 72.0667],
            ['name_uz' => 'Rishton', 'name_ru' => 'Риштан', 'region' => 'Farg\'ona', 'type' => 'tuman', 'latitude' => 40.3500, 'longitude' => 71.2833],
            ['name_uz' => 'So\'x', 'name_ru' => 'Сох', 'region' => 'Farg\'ona', 'type' => 'tuman', 'latitude' => 39.9667, 'longitude' => 71.1333],
            ['name_uz' => 'Toshloq', 'name_ru' => 'Ташлак', 'region' => 'Farg\'ona', 'type' => 'tuman', 'latitude' => 40.5500, 'longitude' => 71.6833],
            ['name_uz' => 'Uchko\'prik', 'name_ru' => 'Учкуприк', 'region' => 'Farg\'ona', 'type' => 'tuman', 'latitude' => 40.5167, 'longitude' => 71.0500],
            ['name_uz' => 'Yozyovon', 'name_ru' => 'Язъяван', 'region' => 'Farg\'ona', 'type' => 'tuman', 'latitude' => 40.6333, 'longitude' => 71.7167],

            // Namangan viloyati
            ['name_uz' => 'Namangan', 'name_ru' => 'Наманган', 'region' => 'Namangan', 'type' => 'shahar', 'latitude' => 40.9983, 'longitude' => 71.6726],
            ['name_uz' => 'Chortoq', 'name_ru' => 'Чартак', 'region' => 'Namangan', 'type' => 'tuman', 'latitude' => 41.0667, 'longitude' => 71.5833],
            ['name_uz' => 'Chust', 'name_ru' => 'Чуст', 'region' => 'Namangan', 'type' => 'tuman', 'latitude' => 41.0000, 'longitude' => 71.2333],
            ['name_uz' => 'Kosonsoy', 'name_ru' => 'Касансай', 'region' => 'Namangan', 'type' => 'tuman', 'latitude' => 41.2333, 'longitude' => 71.5667],
            ['name_uz' => 'Mingbuloq', 'name_ru' => 'Мингбулак', 'region' => 'Namangan', 'type' => 'tuman', 'latitude' => 40.7667, 'longitude' => 71.4000],
            ['name_uz' => 'Namangan', 'name_ru' => 'Наманган', 'region' => 'Namangan', 'type' => 'tuman', 'latitude' => 41.0167, 'longitude' => 71.6833],
            ['name_uz' => 'Norin', 'name_ru' => 'Нарын', 'region' => 'Namangan', 'type' => 'tuman', 'latitude' => 41.0500, 'longitude' => 71.4000],
            ['name_uz' => 'Pop', 'name_ru' => 'Пап', 'region' => 'Namangan', 'type' => 'tuman', 'latitude' => 40.8833, 'longitude' => 71.1167],
            ['name_uz' => 'To\'raqo\'rg\'on', 'name_ru' => 'Туракурган', 'region' => 'Namangan', 'type' => 'tuman', 'latitude' => 41.0167, 'longitude' => 71.5167],
            ['name_uz' => 'Uchqo\'rg\'on', 'name_ru' => 'Учкурган', 'region' => 'Namangan', 'type' => 'tuman', 'latitude' => 41.1167, 'longitude' => 71.0333],
            ['name_uz' => 'Yangiqo\'rg\'on', 'name_ru' => 'Янгикурган', 'region' => 'Namangan', 'type' => 'tuman', 'latitude' => 41.1833, 'longitude' => 71.7167],

            // Samarqand viloyati
            ['name_uz' => 'Samarqand', 'name_ru' => 'Самарканд', 'region' => 'Samarqand', 'type' => 'shahar', 'latitude' => 39.6542, 'longitude' => 66.9597],
            ['name_uz' => 'Kattaqo\'rg\'on', 'name_ru' => 'Каттакурган', 'region' => 'Samarqand', 'type' => 'shahar', 'latitude' => 39.9000, 'longitude' => 66.2667],
            ['name_uz' => 'Bulung\'ur', 'name_ru' => 'Булунгур', 'region' => 'Samarqand', 'type' => 'tuman', 'latitude' => 39.7667, 'longitude' => 67.2667],
            ['name_uz' => 'Ishtixon', 'name_ru' => 'Иштыхан', 'region' => 'Samarqand', 'type' => 'tuman', 'latitude' => 39.9833, 'longitude' => 66.5167],
            ['name_uz' => 'Jomboy', 'name_ru' => 'Джамбай', 'region' => 'Samarqand', 'type' => 'tuman', 'latitude' => 39.7167, 'longitude' => 66.9167],
            ['name_uz' => 'Narpay', 'name_ru' => 'Нарпай', 'region' => 'Samarqand', 'type' => 'tuman', 'latitude' => 39.9167, 'longitude' => 66.5333],
            ['name_uz' => 'Nurobod', 'name_ru' => 'Нурабад', 'region' => 'Samarqand', 'type' => 'tuman', 'latitude' => 39.5667, 'longitude' => 66.6333],
            ['name_uz' => 'Oqdaryo', 'name_ru' => 'Акдарья', 'region' => 'Samarqand', 'type' => 'tuman', 'latitude' => 39.7333, 'longitude' => 66.7333],
            ['name_uz' => 'Pastdarg\'om', 'name_ru' => 'Пастдаргом', 'region' => 'Samarqand', 'type' => 'tuman', 'latitude' => 39.5833, 'longitude' => 66.5667],
            ['name_uz' => 'Paxtachi', 'name_ru' => 'Пахтачи', 'region' => 'Samarqand', 'type' => 'tuman', 'latitude' => 39.9500, 'longitude' => 66.0333],
            ['name_uz' => 'Payariq', 'name_ru' => 'Пайарык', 'region' => 'Samarqand', 'type' => 'tuman', 'latitude' => 39.5500, 'longitude' => 67.1333],
            ['name_uz' => 'Samarqand', 'name_ru' => 'Самарканд', 'region' => 'Samarqand', 'type' => 'tuman', 'latitude' => 39.6833, 'longitude' => 66.9833],
            ['name_uz' => 'Toyloq', 'name_ru' => 'Тайлак', 'region' => 'Samarqand', 'type' => 'tuman', 'latitude' => 39.4833, 'longitude' => 67.2000],
            ['name_uz' => 'Urgut', 'name_ru' => 'Ургут', 'region' => 'Samarqand', 'type' => 'tuman', 'latitude' => 39.4000, 'longitude' => 67.2333],

            // Qashqadaryo viloyati
            ['name_uz' => 'Qarshi', 'name_ru' => 'Карши', 'region' => 'Qashqadaryo', 'type' => 'shahar', 'latitude' => 38.8600, 'longitude' => 65.7983],
            ['name_uz' => 'Shahrisabz', 'name_ru' => 'Шахрисабз', 'region' => 'Qashqadaryo', 'type' => 'shahar', 'latitude' => 39.0500, 'longitude' => 66.8333],
            ['name_uz' => 'Chiroqchi', 'name_ru' => 'Чиракчи', 'region' => 'Qashqadaryo', 'type' => 'tuman', 'latitude' => 39.0333, 'longitude' => 66.5667],
            ['name_uz' => 'Dehqonobod', 'name_ru' => 'Дехканабад', 'region' => 'Qashqadaryo', 'type' => 'tuman', 'latitude' => 38.3500, 'longitude' => 66.5000],
            ['name_uz' => 'G\'uzor', 'name_ru' => 'Гузар', 'region' => 'Qashqadaryo', 'type' => 'tuman', 'latitude' => 38.6167, 'longitude' => 66.2333],
            ['name_uz' => 'Kasbi', 'name_ru' => 'Касби', 'region' => 'Qashqadaryo', 'type' => 'tuman', 'latitude' => 38.9167, 'longitude' => 65.4667],
            ['name_uz' => 'Kitob', 'name_ru' => 'Китаб', 'region' => 'Qashqadaryo', 'type' => 'tuman', 'latitude' => 39.1333, 'longitude' => 66.8833],
            ['name_uz' => 'Ko\'kdala', 'name_ru' => 'Кукдала', 'region' => 'Qashqadaryo', 'type' => 'tuman', 'latitude' => 39.2000, 'longitude' => 66.9000],
            ['name_uz' => 'Mirishkor', 'name_ru' => 'Миришкор', 'region' => 'Qashqadaryo', 'type' => 'tuman', 'latitude' => 38.8667, 'longitude' => 65.2833],
            ['name_uz' => 'Muborak', 'name_ru' => 'Мубарек', 'region' => 'Qashqadaryo', 'type' => 'tuman', 'latitude' => 39.1667, 'longitude' => 65.2500],
            ['name_uz' => 'Nishon', 'name_ru' => 'Нишан', 'region' => 'Qashqadaryo', 'type' => 'tuman', 'latitude' => 38.5333, 'longitude' => 65.7667],
            ['name_uz' => 'Qarshi', 'name_ru' => 'Карши', 'region' => 'Qashqadaryo', 'type' => 'tuman', 'latitude' => 38.8833, 'longitude' => 65.8167],
            ['name_uz' => 'Qamashi', 'name_ru' => 'Камаши', 'region' => 'Qashqadaryo', 'type' => 'tuman', 'latitude' => 38.8000, 'longitude' => 66.6167],
            ['name_uz' => 'Yakkabog\'', 'name_ru' => 'Яккабаг', 'region' => 'Qashqadaryo', 'type' => 'tuman', 'latitude' => 39.0833, 'longitude' => 66.7833],

            // Surxondaryo viloyati
            ['name_uz' => 'Termiz', 'name_ru' => 'Термез', 'region' => 'Surxondaryo', 'type' => 'shahar', 'latitude' => 37.2242, 'longitude' => 67.2783],
            ['name_uz' => 'Angor', 'name_ru' => 'Ангор', 'region' => 'Surxondaryo', 'type' => 'tuman', 'latitude' => 37.5667, 'longitude' => 67.1667],
            ['name_uz' => 'Boysun', 'name_ru' => 'Байсун', 'region' => 'Surxondaryo', 'type' => 'tuman', 'latitude' => 38.2000, 'longitude' => 67.2000],
            ['name_uz' => 'Denov', 'name_ru' => 'Денау', 'region' => 'Surxondaryo', 'type' => 'tuman', 'latitude' => 38.2667, 'longitude' => 67.8833],
            ['name_uz' => 'Jarqo\'rg\'on', 'name_ru' => 'Джаркурган', 'region' => 'Surxondaryo', 'type' => 'tuman', 'latitude' => 37.5167, 'longitude' => 67.4167],
            ['name_uz' => 'Muzrobod', 'name_ru' => 'Музрабад', 'region' => 'Surxondaryo', 'type' => 'tuman', 'latitude' => 37.5000, 'longitude' => 67.6500],
            ['name_uz' => 'Oltinsoy', 'name_ru' => 'Алтынсай', 'region' => 'Surxondaryo', 'type' => 'tuman', 'latitude' => 38.2167, 'longitude' => 67.5000],
            ['name_uz' => 'Qiziriq', 'name_ru' => 'Кизирик', 'region' => 'Surxondaryo', 'type' => 'tuman', 'latitude' => 37.7167, 'longitude' => 67.1333],
            ['name_uz' => 'Qumqo\'rg\'on', 'name_ru' => 'Кумкурган', 'region' => 'Surxondaryo', 'type' => 'tuman', 'latitude' => 37.8333, 'longitude' => 67.0167],
            ['name_uz' => 'Sariosiyo', 'name_ru' => 'Сариасия', 'region' => 'Surxondaryo', 'type' => 'tuman', 'latitude' => 38.4000, 'longitude' => 67.8667],
            ['name_uz' => 'Sherobod', 'name_ru' => 'Шерабад', 'region' => 'Surxondaryo', 'type' => 'tuman', 'latitude' => 37.6667, 'longitude' => 67.0000],
            ['name_uz' => 'Sho\'rchi', 'name_ru' => 'Шурчи', 'region' => 'Surxondaryo', 'type' => 'tuman', 'latitude' => 37.9833, 'longitude' => 67.7500],
            ['name_uz' => 'Termiz', 'name_ru' => 'Термез', 'region' => 'Surxondaryo', 'type' => 'tuman', 'latitude' => 37.2500, 'longitude' => 67.3000],

            // Jizzax viloyati
            ['name_uz' => 'Jizzax', 'name_ru' => 'Джизак', 'region' => 'Jizzax', 'type' => 'shahar', 'latitude' => 40.1158, 'longitude' => 67.8422],
            ['name_uz' => 'Arnasoy', 'name_ru' => 'Арнасай', 'region' => 'Jizzax', 'type' => 'tuman', 'latitude' => 40.6167, 'longitude' => 68.3167],
            ['name_uz' => 'Baxmal', 'name_ru' => 'Бахмал', 'region' => 'Jizzax', 'type' => 'tuman', 'latitude' => 39.7000, 'longitude' => 68.1667],
            ['name_uz' => 'Do\'stlik', 'name_ru' => 'Дустлик', 'region' => 'Jizzax', 'type' => 'tuman', 'latitude' => 40.5333, 'longitude' => 67.9500],
            ['name_uz' => 'Forish', 'name_ru' => 'Фариш', 'region' => 'Jizzax', 'type' => 'tuman', 'latitude' => 40.3833, 'longitude' => 68.2500],
            ['name_uz' => 'G\'allaorol', 'name_ru' => 'Галляарал', 'region' => 'Jizzax', 'type' => 'tuman', 'latitude' => 40.5333, 'longitude' => 68.0333],
            ['name_uz' => 'Mirzacho\'l', 'name_ru' => 'Мирзачуль', 'region' => 'Jizzax', 'type' => 'tuman', 'latitude' => 40.7333, 'longitude' => 68.0667],
            ['name_uz' => 'Paxtakor', 'name_ru' => 'Пахтакор', 'region' => 'Jizzax', 'type' => 'tuman', 'latitude' => 40.3000, 'longitude' => 67.9333],
            ['name_uz' => 'Yangiobod', 'name_ru' => 'Янгиабад', 'region' => 'Jizzax', 'type' => 'tuman', 'latitude' => 39.8333, 'longitude' => 68.3333],
            ['name_uz' => 'Zafarobod', 'name_ru' => 'Зафарабад', 'region' => 'Jizzax', 'type' => 'tuman', 'latitude' => 40.5500, 'longitude' => 68.3000],
            ['name_uz' => 'Zarbdor', 'name_ru' => 'Зарбдор', 'region' => 'Jizzax', 'type' => 'tuman', 'latitude' => 40.5667, 'longitude' => 67.7000],
            ['name_uz' => 'Zomin', 'name_ru' => 'Заамин', 'region' => 'Jizzax', 'type' => 'tuman', 'latitude' => 39.9500, 'longitude' => 68.4167],

            // Navoiy viloyati
            ['name_uz' => 'Navoiy', 'name_ru' => 'Навои', 'region' => 'Navoiy', 'type' => 'shahar', 'latitude' => 40.0844, 'longitude' => 65.3792],
            ['name_uz' => 'Zarafshon', 'name_ru' => 'Зарафшан', 'region' => 'Navoiy', 'type' => 'shahar', 'latitude' => 41.5667, 'longitude' => 64.2000],
            ['name_uz' => 'Karmana', 'name_ru' => 'Кармана', 'region' => 'Navoiy', 'type' => 'tuman', 'latitude' => 40.1333, 'longitude' => 65.3667],
            ['name_uz' => 'Konimex', 'name_ru' => 'Канимех', 'region' => 'Navoiy', 'type' => 'tuman', 'latitude' => 40.3667, 'longitude' => 64.9167],
            ['name_uz' => 'Navbahor', 'name_ru' => 'Навбахор', 'region' => 'Navoiy', 'type' => 'tuman', 'latitude' => 40.0833, 'longitude' => 65.5667],
            ['name_uz' => 'Nurota', 'name_ru' => 'Нурата', 'region' => 'Navoiy', 'type' => 'tuman', 'latitude' => 40.5667, 'longitude' => 65.6833],
            ['name_uz' => 'Qiziltepa', 'name_ru' => 'Кызылтепа', 'region' => 'Navoiy', 'type' => 'tuman', 'latitude' => 40.0167, 'longitude' => 65.1333],
            ['name_uz' => 'Tomdi', 'name_ru' => 'Тамды', 'region' => 'Navoiy', 'type' => 'tuman', 'latitude' => 42.1333, 'longitude' => 64.6167],
            ['name_uz' => 'Uchquduq', 'name_ru' => 'Учкудук', 'region' => 'Navoiy', 'type' => 'tuman', 'latitude' => 42.1500, 'longitude' => 63.5500],
            ['name_uz' => 'Xatirchi', 'name_ru' => 'Хатырчи', 'region' => 'Navoiy', 'type' => 'tuman', 'latitude' => 40.2167, 'longitude' => 65.7667],

            // Sirdaryo viloyati
            ['name_uz' => 'Guliston', 'name_ru' => 'Гулистан', 'region' => 'Sirdaryo', 'type' => 'shahar', 'latitude' => 40.4897, 'longitude' => 68.7842],
            ['name_uz' => 'Yangiyer', 'name_ru' => 'Янгиер', 'region' => 'Sirdaryo', 'type' => 'shahar', 'latitude' => 40.2833, 'longitude' => 68.8333],
            ['name_uz' => 'Shirin', 'name_ru' => 'Ширин', 'region' => 'Sirdaryo', 'type' => 'shahar', 'latitude' => 40.4500, 'longitude' => 68.6667],
            ['name_uz' => 'Boyovut', 'name_ru' => 'Баяут', 'region' => 'Sirdaryo', 'type' => 'tuman', 'latitude' => 40.0833, 'longitude' => 68.8667],
            ['name_uz' => 'Guliston', 'name_ru' => 'Гулистан', 'region' => 'Sirdaryo', 'type' => 'tuman', 'latitude' => 40.5000, 'longitude' => 68.8000],
            ['name_uz' => 'Mirzaobod', 'name_ru' => 'Мирзаабад', 'region' => 'Sirdaryo', 'type' => 'tuman', 'latitude' => 40.5833, 'longitude' => 68.7333],
            ['name_uz' => 'Oqoltin', 'name_ru' => 'Акалтын', 'region' => 'Sirdaryo', 'type' => 'tuman', 'latitude' => 40.3500, 'longitude' => 68.5500],
            ['name_uz' => 'Sardoba', 'name_ru' => 'Сардоба', 'region' => 'Sirdaryo', 'type' => 'tuman', 'latitude' => 40.1333, 'longitude' => 68.5500],
            ['name_uz' => 'Sayxunobod', 'name_ru' => 'Сайхунабад', 'region' => 'Sirdaryo', 'type' => 'tuman', 'latitude' => 40.2667, 'longitude' => 68.9500],
            ['name_uz' => 'Xovos', 'name_ru' => 'Хавас', 'region' => 'Sirdaryo', 'type' => 'tuman', 'latitude' => 40.5500, 'longitude' => 68.8833],

            // Xorazm viloyati
            ['name_uz' => 'Urganch', 'name_ru' => 'Ургенч', 'region' => 'Xorazm', 'type' => 'shahar', 'latitude' => 41.5500, 'longitude' => 60.6333],
            ['name_uz' => 'Xiva', 'name_ru' => 'Хива', 'region' => 'Xorazm', 'type' => 'shahar', 'latitude' => 41.3833, 'longitude' => 60.3500],
            ['name_uz' => 'Bog\'ot', 'name_ru' => 'Багат', 'region' => 'Xorazm', 'type' => 'tuman', 'latitude' => 41.4333, 'longitude' => 60.7000],
            ['name_uz' => 'Gurlan', 'name_ru' => 'Гурлен', 'region' => 'Xorazm', 'type' => 'tuman', 'latitude' => 41.7333, 'longitude' => 60.6333],
            ['name_uz' => 'Hazorasp', 'name_ru' => 'Хазарасп', 'region' => 'Xorazm', 'type' => 'tuman', 'latitude' => 41.3000, 'longitude' => 61.0667],
            ['name_uz' => 'Qo\'shko\'pir', 'name_ru' => 'Кушкупыр', 'region' => 'Xorazm', 'type' => 'tuman', 'latitude' => 41.5333, 'longitude' => 60.3333],
            ['name_uz' => 'Shovot', 'name_ru' => 'Шават', 'region' => 'Xorazm', 'type' => 'tuman', 'latitude' => 41.6333, 'longitude' => 60.7333],
            ['name_uz' => 'Tuproqqal\'a', 'name_ru' => 'Тупроккала', 'region' => 'Xorazm', 'type' => 'tuman', 'latitude' => 41.7000, 'longitude' => 60.8167],
            ['name_uz' => 'Urganch', 'name_ru' => 'Ургенч', 'region' => 'Xorazm', 'type' => 'tuman', 'latitude' => 41.5667, 'longitude' => 60.6500],
            ['name_uz' => 'Xiva', 'name_ru' => 'Хива', 'region' => 'Xorazm', 'type' => 'tuman', 'latitude' => 41.4000, 'longitude' => 60.3667],
            ['name_uz' => 'Xonqa', 'name_ru' => 'Ханка', 'region' => 'Xorazm', 'type' => 'tuman', 'latitude' => 41.5000, 'longitude' => 60.9000],
            ['name_uz' => 'Yangiariq', 'name_ru' => 'Янгиарык', 'region' => 'Xorazm', 'type' => 'tuman', 'latitude' => 41.4333, 'longitude' => 60.5833],
            ['name_uz' => 'Yangibozor', 'name_ru' => 'Янгибазар', 'region' => 'Xorazm', 'type' => 'tuman', 'latitude' => 41.7333, 'longitude' => 60.5500],

            // Qoraqalpog'iston Respublikasi
            ['name_uz' => 'Nukus', 'name_ru' => 'Нукус', 'region' => 'Qoraqalpog\'iston', 'type' => 'shahar', 'latitude' => 42.4628, 'longitude' => 59.6003],
            ['name_uz' => 'Amudaryo', 'name_ru' => 'Амударья', 'region' => 'Qoraqalpog\'iston', 'type' => 'tuman', 'latitude' => 41.9333, 'longitude' => 60.4167],
            ['name_uz' => 'Beruniy', 'name_ru' => 'Беруни', 'region' => 'Qoraqalpog\'iston', 'type' => 'tuman', 'latitude' => 41.6833, 'longitude' => 60.7500],
            ['name_uz' => 'Chimboy', 'name_ru' => 'Чимбай', 'region' => 'Qoraqalpog\'iston', 'type' => 'tuman', 'latitude' => 42.9333, 'longitude' => 59.7667],
            ['name_uz' => 'Ellikqal\'a', 'name_ru' => 'Элликкала', 'region' => 'Qoraqalpog\'iston', 'type' => 'tuman', 'latitude' => 41.7500, 'longitude' => 60.7167],
            ['name_uz' => 'Kegeyli', 'name_ru' => 'Кегейли', 'region' => 'Qoraqalpog\'iston', 'type' => 'tuman', 'latitude' => 42.7833, 'longitude' => 58.6833],
            ['name_uz' => 'Mo\'ynoq', 'name_ru' => 'Муйнак', 'region' => 'Qoraqalpog\'iston', 'type' => 'tuman', 'latitude' => 43.7667, 'longitude' => 58.6833],
            ['name_uz' => 'Nukus', 'name_ru' => 'Нукус', 'region' => 'Qoraqalpog\'iston', 'type' => 'tuman', 'latitude' => 42.4833, 'longitude' => 59.6167],
            ['name_uz' => 'Qonliko\'l', 'name_ru' => 'Канлыкуль', 'region' => 'Qoraqalpog\'iston', 'type' => 'tuman', 'latitude' => 42.6333, 'longitude' => 59.3000],
            ['name_uz' => 'Qo\'ng\'irot', 'name_ru' => 'Кунград', 'region' => 'Qoraqalpog\'iston', 'type' => 'tuman', 'latitude' => 43.0833, 'longitude' => 58.7000],
            ['name_uz' => 'Shumanay', 'name_ru' => 'Шуманай', 'region' => 'Qoraqalpog\'iston', 'type' => 'tuman', 'latitude' => 42.5500, 'longitude' => 59.3667],
            ['name_uz' => 'Taxtako\'pir', 'name_ru' => 'Тахтакупыр', 'region' => 'Qoraqalpog\'iston', 'type' => 'tuman', 'latitude' => 42.0333, 'longitude' => 56.9833],
            ['name_uz' => 'To\'rtko\'l', 'name_ru' => 'Турткуль', 'region' => 'Qoraqalpog\'iston', 'type' => 'tuman', 'latitude' => 41.5500, 'longitude' => 60.6167],
            ['name_uz' => 'Xo\'jayli', 'name_ru' => 'Ходжейли', 'region' => 'Qoraqalpog\'iston', 'type' => 'tuman', 'latitude' => 42.5500, 'longitude' => 59.4500],
        ];

        foreach ($cities as $city) {
            City::create(array_merge($city, ['is_active' => true]));
        }
    }
}
