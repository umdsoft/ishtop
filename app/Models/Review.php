<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'employer_profile_id', 'worker_user_id', 'rating',
        'comment', 'reply', 'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'replied_at' => 'datetime',
        ];
    }

    public function employerProfile(): BelongsTo
    {
        return $this->belongsTo(EmployerProfile::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_user_id');
    }
}
