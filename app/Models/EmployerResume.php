<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployerResume extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'employer_profile_id', 'owner_name', 'position', 'experience_years',
        'education', 'achievements', 'photo_url', 'linkedin_url', 'bio',
    ];

    protected function casts(): array
    {
        return [
            'achievements' => 'array',
            'experience_years' => 'integer',
        ];
    }

    public function employerProfile(): BelongsTo
    {
        return $this->belongsTo(EmployerProfile::class);
    }
}
