<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannerImpression extends Model
{
    public $timestamps = false;
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'banner_id', 'user_id', 'placement', 'action', 'ip',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class);
    }
}
