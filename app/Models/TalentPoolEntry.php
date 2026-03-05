<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TalentPoolEntry extends Model
{
    use HasUuids;

    protected $fillable = [
        'recruiter_user_id', 'worker_profile_id', 'notes', 'tags', 'source',
    ];

    protected function casts(): array
    {
        return ['tags' => 'array'];
    }

    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_user_id');
    }

    public function workerProfile(): BelongsTo
    {
        return $this->belongsTo(WorkerProfile::class);
    }
}
