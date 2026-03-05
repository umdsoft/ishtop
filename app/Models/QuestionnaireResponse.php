<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireResponse extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'questionnaire_id', 'application_id', 'user_id', 'total_score',
        'knockout_passed', 'status', 'time_spent_seconds',
        'completed_at', 'scored_at', 'reviewed_at', 'reviewer_notes',
    ];

    protected function casts(): array
    {
        return [
            'total_score' => 'decimal:2',
            'knockout_passed' => 'boolean',
            'time_spent_seconds' => 'integer',
            'completed_at' => 'datetime',
            'scored_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ResponseAnswer::class, 'response_id');
    }

    public function scoreColor(): string
    {
        if (!$this->knockout_passed) return 'danger';
        if ($this->total_score >= 80) return 'success';
        if ($this->total_score >= 50) return 'warning';
        return 'danger';
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'scored', 'reviewed']);
    }
}
