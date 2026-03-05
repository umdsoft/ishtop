<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'question_id', 'sort_order', 'value', 'label_uz', 'label_ru',
        'is_correct', 'score_value',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_correct' => 'boolean',
            'score_value' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function label(string $lang = 'uz'): string
    {
        return $lang === 'ru' && $this->label_ru ? $this->label_ru : $this->label_uz;
    }
}
