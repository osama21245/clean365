<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\UserManagement\Entities\User;

class AdBroadcastAck extends Model
{
    protected $fillable = [
        'ad_broadcast_id',
        'user_id',
        'event',
    ];

    public function adBroadcast(): BelongsTo
    {
        return $this->belongsTo(AdBroadcast::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
