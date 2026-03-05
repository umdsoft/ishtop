<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseAnswer extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'response_id', 'question_id', 'answer_value', 'score',
        'is_knockout_failed', 'manual_score', 'manual_scored_by',
    ];

    protected function casts(): array
    {
        return [
            'answer_value' => 'array',
            'score' => 'decimal:2',
            'is_knockout_failed' => 'boolean',
            'manual_score' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireResponse::class, 'response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function getFinalScore(): ?float
    {
        return $this->manual_score !== null ? (float) $this->manual_score : $this->score;
    }
}
