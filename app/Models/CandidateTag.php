<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateTag extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['application_id', 'tag_id'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(RecruiterTag::class, 'tag_id');
    }
}
