<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name_uz', 'name_ru', 'region', 'latitude', 'longitude', 'is_active',
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
}
