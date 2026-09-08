<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\UserManagement\Entities\User;

class UserInboxNotification extends Model
{
    protected $table = 'user_inbox_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'url',
        'view',
        'type',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
