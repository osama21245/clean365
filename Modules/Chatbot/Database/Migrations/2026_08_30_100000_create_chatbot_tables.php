<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chatbot_conversations')) {
            Schema::create('chatbot_conversations', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->uuid('user_id')->nullable()->index();
                $table->string('guest_token')->nullable()->index();
                $table->uuid('zone_id')->nullable()->index();
                $table->string('status')->default('open')->index();
                $table->timestamp('last_message_at')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['guest_token', 'status']);
                $table->index(['user_id', 'status']);
            });
        }

        if (! Schema::hasTable('chatbot_messages')) {
            Schema::create('chatbot_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('chatbot_conversations')->cascadeOnDelete();
                $table->string('sender_type'); // user, ai, system
                $table->text('content');
                $table->string('content_type')->default('text');
                $table->string('intent')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['conversation_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_messages');
        Schema::dropIfExists('chatbot_conversations');
    }
};
