<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Questionnaire extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'vacancy_id', 'title', 'description', 'is_required',
        'time_limit_minutes', 'passing_score', 'auto_reject_below',
        'is_template', 'template_name', 'questions_count',
        'responses_count', 'avg_score',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_template' => 'boolean',
            'time_limit_minutes' => 'integer',
            'passing_score' => 'integer',
            'auto_reject_below' => 'integer',
            'questions_count' => 'integer',
            'responses_count' => 'integer',
            'avg_score' => 'decimal:2',
        ];
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class);
    }

    public function updateStats(): void
    {
        $this->update([
            'questions_count' => $this->questions()->count(),
            'responses_count' => $this->responses()->where('status', 'scored')->count(),
            'avg_score' => $this->responses()->where('status', 'scored')->avg('total_score') ?? 0,
        ]);
    }
}
