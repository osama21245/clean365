<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('job_batches')) {
            Schema::create('job_batches', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->integer('total_jobs');
                $table->integer('pending_jobs');
                $table->integer('failed_jobs');
                $table->longText('failed_job_ids');
                $table->mediumText('options')->nullable();
                $table->integer('cancelled_at')->nullable();
                $table->integer('created_at');
                $table->integer('finished_at')->nullable();
            });
        }

        Schema::create('ad_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->uuid('send_id')->nullable()->unique();
            $table->string('audience', 128);
            $table->string('content_target', 32)->nullable()->index();
            $table->uuid('category_id')->nullable();
            $table->uuid('subcategory_id')->nullable();
            $table->uuid('service_id')->nullable();
            $table->text('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('zone_ids')->nullable();
            $table->json('explicit_user_ids')->nullable();
            $table->string('fcm_status', 32)->nullable();
            $table->unsignedInteger('recipient_estimate')->nullable();
            $table->unsignedInteger('tokens_targeted')->default(0);
            $table->unsignedInteger('tokens_success')->default(0);
            $table->unsignedInteger('tokens_failure')->default(0);
            $table->unsignedSmallInteger('topic_dispatches_ok')->default(0);
            $table->unsignedSmallInteger('topic_dispatches_total')->default(0);
            $table->string('laravel_batch_id')->nullable();
            $table->unsignedInteger('opened_count')->default(0);
            $table->json('fcm_summary')->nullable();
            $table->timestamps();
        });

        Schema::create('ad_broadcast_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_broadcast_id')->constrained('ad_broadcasts')->cascadeOnDelete();
            $table->string('dispatch_key', 64)->unique();
            $table->string('dispatch_type', 16);
            $table->string('status', 16)->default('pending');
            $table->json('metrics')->nullable();
            $table->timestamps();
        });

        Schema::create('ad_broadcast_acks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_broadcast_id')->constrained('ad_broadcasts')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('event', 32)->default('opened');
            $table->timestamps();

            $table->unique(['ad_broadcast_id', 'user_id', 'event']);
        });

        $defaults = [
            'ai_push_enabled' => false,
            'ai_push_prompt' => '',
            'ai_push_frequency' => 'weekly',
            'ai_push_time' => '09:00',
            'ai_push_send_times' => ['09:00'],
            'ai_push_audiences' => ['customers'],
            'ai_push_last_sent_at' => null,
            'ai_push_recent_topics' => [],
            'ai_push_last_error' => null,
            'ai_push_last_title' => null,
            'ai_push_service_id' => null,
            'ai_push_last_cycle_at' => null,
            'ai_push_daily_sent_slots' => null,
        ];

        if (Schema::hasTable('business_settings')) {
            BusinessSettings::updateOrCreate(
                [
                    'key_name' => 'ai_push_settings',
                    'settings_type' => 'notification_config',
                ],
                [
                    'live_values' => $defaults,
                    'test_values' => $defaults,
                    'mode' => 'live',
                    'is_active' => 1,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_broadcast_acks');
        Schema::dropIfExists('ad_broadcast_dispatches');
        Schema::dropIfExists('ad_broadcasts');

        if (Schema::hasTable('business_settings')) {
            BusinessSettings::where('key_name', 'ai_push_settings')
                ->where('settings_type', 'notification_config')
                ->delete();
        }
    }
};
