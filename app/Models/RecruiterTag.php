<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruiterTag extends Model
{
    use HasUuids;

    protected $fillable = ['user_id', 'name', 'color', 'usage_count'];

    protected function casts(): array
    {
        return ['usage_count' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->belongsToMany(Application::class, 'candidate_tags', 'tag_id', 'application_id');
    }
}
