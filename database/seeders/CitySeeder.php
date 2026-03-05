<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            // Xorazm viloyati (asosiy — launch)
            ['name_uz' => 'Urganch', 'name_ru' => 'Ургенч', 'region' => 'Xorazm', 'latitude' => 41.5500, 'longitude' => 60.6333],
            ['name_uz' => 'Xiva', 'name_ru' => 'Хива', 'region' => 'Xorazm', 'latitude' => 41.3786, 'longitude' => 60.3639],
            ['name_uz' => 'Shovot', 'name_ru' => 'Шават', 'region' => 'Xorazm', 'latitude' => 41.6500, 'longitude' => 60.6167],
            ['name_uz' => 'Bog\'ot', 'name_ru' => 'Багат', 'region' => 'Xorazm', 'latitude' => 41.4333, 'longitude' => 60.7000],
            ['name_uz' => 'Gurlan', 'name_ru' => 'Гурлен', 'region' => 'Xorazm', 'latitude' => 41.7333, 'longitude' => 60.6333],
            ['name_uz' => 'Xonqa', 'name_ru' => 'Ханка', 'region' => 'Xorazm', 'latitude' => 41.5167, 'longitude' => 60.7667],
            ['name_uz' => 'Hazorasp', 'name_ru' => 'Хазарасп', 'region' => 'Xorazm', 'latitude' => 41.3000, 'longitude' => 61.0667],
            ['name_uz' => 'Yangiariq', 'name_ru' => 'Янгиарык', 'region' => 'Xorazm', 'latitude' => 41.3833, 'longitude' => 60.6333],
            ['name_uz' => 'Qo\'shko\'pir', 'name_ru' => 'Кушкупыр', 'region' => 'Xorazm', 'latitude' => 41.5333, 'longitude' => 60.3333],
            ['name_uz' => 'Tuproqqal\'a', 'name_ru' => 'Тупроккала', 'region' => 'Xorazm', 'latitude' => 41.5667, 'longitude' => 60.8167],
            // Toshkent (kengayish)
            ['name_uz' => 'Toshkent', 'name_ru' => 'Ташкент', 'region' => 'Toshkent', 'latitude' => 41.2995, 'longitude' => 69.2401],
            ['name_uz' => 'Chirchiq', 'name_ru' => 'Чирчик', 'region' => 'Toshkent', 'latitude' => 41.4689, 'longitude' => 69.5822],
            ['name_uz' => 'Olmaliq', 'name_ru' => 'Алмалык', 'region' => 'Toshkent', 'latitude' => 40.8500, 'longitude' => 69.6000],
            ['name_uz' => 'Angren', 'name_ru' => 'Ангрен', 'region' => 'Toshkent', 'latitude' => 41.0167, 'longitude' => 70.1333],
            // Samarqand
            ['name_uz' => 'Samarqand', 'name_ru' => 'Самарканд', 'region' => 'Samarqand', 'latitude' => 39.6542, 'longitude' => 66.9597],
            // Buxoro
            ['name_uz' => 'Buxoro', 'name_ru' => 'Бухара', 'region' => 'Buxoro', 'latitude' => 39.7747, 'longitude' => 64.4286],
            // Navoiy
            ['name_uz' => 'Navoiy', 'name_ru' => 'Навои', 'region' => 'Navoiy', 'latitude' => 40.0844, 'longitude' => 65.3792],
            // Andijon
            ['name_uz' => 'Andijon', 'name_ru' => 'Андижан', 'region' => 'Andijon', 'latitude' => 40.7833, 'longitude' => 72.3333],
            // Farg'ona
            ['name_uz' => 'Farg\'ona', 'name_ru' => 'Фергана', 'region' => 'Farg\'ona', 'latitude' => 40.3842, 'longitude' => 71.7889],
            // Namangan
            ['name_uz' => 'Namangan', 'name_ru' => 'Наманган', 'region' => 'Namangan', 'latitude' => 40.9983, 'longitude' => 71.6726],
            // Nukus
            ['name_uz' => 'Nukus', 'name_ru' => 'Нукус', 'region' => 'Qoraqalpog\'iston', 'latitude' => 42.4628, 'longitude' => 59.6003],
            // Qashqadaryo
            ['name_uz' => 'Qarshi', 'name_ru' => 'Карши', 'region' => 'Qashqadaryo', 'latitude' => 38.8600, 'longitude' => 65.7983],
            // Surxondaryo
            ['name_uz' => 'Termiz', 'name_ru' => 'Термез', 'region' => 'Surxondaryo', 'latitude' => 37.2242, 'longitude' => 67.2783],
            // Jizzax
            ['name_uz' => 'Jizzax', 'name_ru' => 'Джизак', 'region' => 'Jizzax', 'latitude' => 40.1158, 'longitude' => 67.8422],
            // Sirdaryo
            ['name_uz' => 'Guliston', 'name_ru' => 'Гулистан', 'region' => 'Sirdaryo', 'latitude' => 40.4897, 'longitude' => 68.7842],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(
                ['name_uz' => $city['name_uz'], 'region' => $city['region']],
                $city
            );
        }
    }
}
