<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SavedItem extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['user_id', 'saveable_type', 'saveable_id'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function saveable(): MorphTo
    {
        return $this->morphTo();
    }
}
