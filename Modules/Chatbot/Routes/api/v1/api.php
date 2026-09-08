<?php

use Illuminate\Support\Facades\Route;
use Modules\Chatbot\Http\Controllers\Api\V1\Customer\ChatbotController;

/*
|--------------------------------------------------------------------------
| Customer chatbot API
|--------------------------------------------------------------------------
| Prefix: /api/v1/customer/chatbot
| Guest: advise + package discovery
| Auth:  my visits + rebook (+ claim guest threads)
*/

Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
    Route::group(['prefix' => 'chatbot', 'as' => 'chatbot.'], function () {
        // Guest
        Route::post('ack', [ChatbotController::class, 'ack'])->name('ack');
        Route::post('message', [ChatbotController::class, 'message'])->name('message');
        Route::post('conversations', [ChatbotController::class, 'createGuestConversation'])->name('conversations.create');
        Route::get('conversations', [ChatbotController::class, 'guestConversations'])->name('conversations.index');
        Route::get('conversations/{uuid}/messages', [ChatbotController::class, 'messages'])->name('conversations.messages');

        // Authenticated customer
        Route::group(['prefix' => 'user', 'as' => 'user.', 'middleware' => ['auth:api']], function () {
            Route::post('ack', [ChatbotController::class, 'userAck'])->name('ack');
            Route::post('message', [ChatbotController::class, 'userMessage'])->name('message');
            Route::get('conversations', [ChatbotController::class, 'userConversations'])->name('conversations.index');
            Route::get('conversations/{uuid}/messages', [ChatbotController::class, 'userConversationMessages'])->name('conversations.messages');
            Route::post('claim', [ChatbotController::class, 'claimGuest'])->name('claim');
        });
    });
});
