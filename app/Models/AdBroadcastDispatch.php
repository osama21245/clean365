<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdBroadcastDispatch extends Model
{
    protected $fillable = [
        'ad_broadcast_id',
        'dispatch_key',
        'dispatch_type',
        'status',
        'metrics',
    ];

    protected function casts(): array
    {
        return [
            'metrics' => 'array',
        ];
    }

    public function adBroadcast(): BelongsTo
    {
        return $this->belongsTo(AdBroadcast::class);
    }
}
