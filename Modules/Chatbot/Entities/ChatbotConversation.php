<?php

namespace Modules\Chatbot\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\UserManagement\Entities\User;

class ChatbotConversation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'array',
        'last_message_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(ChatbotMessage::class, 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
