<?php

namespace App\Models;

use App\Enums\ApplicationStage;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'vacancy_id', 'worker_id', 'stage', 'cover_letter',
        'questionnaire_score', 'knockout_passed', 'recruiter_rating',
        'matching_score', 'source', 'viewed_at', 'shortlisted_at',
        'interviewed_at', 'offered_at', 'rejected_reason',
    ];

    protected function casts(): array
    {
        return [
            'questionnaire_score' => 'decimal:2',
            'matching_score' => 'decimal:2',
            'knockout_passed' => 'boolean',
            'recruiter_rating' => 'integer',
            'viewed_at' => 'datetime',
            'shortlisted_at' => 'datetime',
            'interviewed_at' => 'datetime',
            'offered_at' => 'datetime',
            'stage' => ApplicationStage::class,
        ];
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerProfile::class, 'worker_id');
    }

    public function questionnaireResponse(): HasOne
    {
        return $this->hasOne(QuestionnaireResponse::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(RecruiterNote::class);
    }

    public function candidateTags(): HasMany
    {
        return $this->hasMany(CandidateTag::class);
    }

    public function tags()
    {
        return $this->belongsToMany(RecruiterTag::class, 'candidate_tags', 'application_id', 'tag_id');
    }

    public function chat(): HasOne
    {
        return $this->hasOne(Chat::class);
    }

    // ── Scopes ──

    public function scopeStage($query, ApplicationStage $stage)
    {
        return $query->where('stage', $stage);
    }

    public function scopeWithScore($query)
    {
        return $query->whereNotNull('questionnaire_score')
            ->orderByDesc('questionnaire_score');
    }

    public function scopeGreenScore($query)
    {
        return $query->where('questionnaire_score', '>=', 80);
    }

    public function scopeKnockoutPassed($query)
    {
        return $query->where('knockout_passed', true);
    }

    // ── Helpers ──

    public function moveToStage(ApplicationStage $stage): void
    {
        $timestampField = match ($stage) {
            ApplicationStage::REVIEWED => 'viewed_at',
            ApplicationStage::SHORTLISTED => 'shortlisted_at',
            ApplicationStage::INTERVIEW => 'interviewed_at',
            ApplicationStage::OFFERED => 'offered_at',
            default => null,
        };

        $data = ['stage' => $stage];
        if ($timestampField) {
            $data[$timestampField] = now();
        }

        $this->update($data);
    }

    public function scoreColor(): string
    {
        if (!$this->knockout_passed) return 'danger';
        if ($this->questionnaire_score === null) return 'gray';
        if ($this->questionnaire_score >= 80) return 'success';
        if ($this->questionnaire_score >= 50) return 'warning';
        return 'danger';
    }
}
