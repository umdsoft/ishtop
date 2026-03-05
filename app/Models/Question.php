<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'questionnaire_id', 'sort_order', 'type', 'text_uz', 'text_ru',
        'is_required', 'weight', 'is_knockout', 'correct_answer', 'scoring_config',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_required' => 'boolean',
            'weight' => 'integer',
            'is_knockout' => 'boolean',
            'correct_answer' => 'array',
            'scoring_config' => 'array',
            'type' => QuestionType::class,
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('sort_order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ResponseAnswer::class);
    }

    public function text(string $lang = 'uz'): string
    {
        return $lang === 'ru' && $this->text_ru ? $this->text_ru : $this->text_uz;
    }
}
