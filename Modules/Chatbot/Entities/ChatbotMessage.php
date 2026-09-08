<?php

namespace Modules\Chatbot\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotMessage extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatbotConversation::class, 'conversation_id');
    }
}
