<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'name', 'type', 'body_uz', 'body_ru',
        'variables', 'is_system',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function render(array $data, string $lang = 'uz'): string
    {
        $body = $lang === 'ru' && $this->body_ru ? $this->body_ru : $this->body_uz;

        foreach ($data as $key => $value) {
            $body = str_replace("{{$key}}", $value, $body);
        }

        return $body;
    }
}
