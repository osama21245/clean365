<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_inbox_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->json('title')->nullable();
            $table->json('description')->nullable();
            $table->string('url')->nullable();
            $table->string('view')->nullable();
            $table->string('type')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_inbox_notifications');
    }
};
