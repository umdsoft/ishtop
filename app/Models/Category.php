<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'slug', 'parent_id', 'name_uz', 'name_ru', 'icon', 'emoji', 'default_skills', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_skills' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function name(string $lang = 'uz'): string
    {
        return $lang === 'ru' ? $this->name_ru : $this->name_uz;
    }

    public function getEmoji(): string
    {
        return $this->emoji ?? '📁';
    }
}
