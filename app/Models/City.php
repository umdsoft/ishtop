<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class City extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name_uz', 'name_ru', 'region', 'type', 'latitude', 'longitude', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('name_uz');
    }

    public function name(string $lang = 'uz'): string
    {
        return $lang === 'ru' ? $this->name_ru : $this->name_uz;
    }

    /**
     * Yagona cache — barcha joylar shu yerdan oladi.
     * 1 soat cache, regions + cities qaytaradi.
     */
    public static function cachedLocations(): array
    {
        return Cache::remember('locations:all', 3600, function () {
            $cities = self::orderBy('region')->orderBy('name_uz')
                ->get(['id', 'name_uz', 'name_ru', 'region', 'type', 'latitude', 'longitude']);

            $regionTranslations = [
                'Toshkent shahri'              => 'Ташкент',
                'Toshkent viloyati'            => 'Ташкентская область',
                'Andijon viloyati'             => 'Андижанская область',
                'Buxoro viloyati'              => 'Бухарская область',
                "Farg'ona viloyati"            => 'Ферганская область',
                'Jizzax viloyati'              => 'Джизакская область',
                'Xorazm viloyati'              => 'Хорезмская область',
                'Namangan viloyati'            => 'Наманганская область',
                'Navoiy viloyati'              => 'Навоийская область',
                'Qashqadaryo viloyati'         => 'Кашкадарьинская область',
                "Qoraqalpog'iston Respublikasi" => 'Республика Каракалпакстан',
                'Samarqand viloyati'           => 'Самаркандская область',
                'Sirdaryo viloyati'            => 'Сырдарьинская область',
                'Surxondaryo viloyati'         => 'Сурхандарьинская область',
            ];

            $regions = $cities->pluck('region')->unique()->values()->map(fn($region) => [
                'key'     => $region,
                'name_uz' => $region,
                'name_ru' => $regionTranslations[$region] ?? $region,
            ]);

            return [
                'regions' => $regions->toArray(),
                'cities'  => $cities->toArray(),
            ];
        });
    }
}
