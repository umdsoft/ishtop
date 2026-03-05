<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewSlot extends Model
{
    use HasUuids;

    protected $fillable = [
        'vacancy_id', 'date', 'start_time', 'end_time',
        'is_booked', 'booked_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_booked' => 'boolean',
        ];
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function bookedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_booked', false)->where('date', '>=', today());
    }
}
