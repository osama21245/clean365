<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_id')->nullable()->index();
            $table->uuid('category_id')->nullable()->index();
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('excerpt')->nullable();
            $table->json('body');
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->string('featured_image')->nullable();
            $table->boolean('generated_by_ai')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('services')->nullOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
        });

        $defaults = [
            'ai_blog_automation_enabled' => 0,
            'ai_blog_daily_limit' => 5,
            'ai_blog_monthly_limit' => 100,
            'ai_blog_prompt' => '',
        ];

        if (Schema::hasTable('business_settings')) {
            foreach ($defaults as $key => $value) {
                BusinessSettings::updateOrCreate(
                    [
                        'key_name' => $key,
                        'settings_type' => 'blog_automation',
                    ],
                    [
                        'live_values' => $value,
                        'test_values' => $value,
                        'mode' => 'live',
                        'is_active' => 1,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_articles');

        BusinessSettings::where('settings_type', 'blog_automation')->delete();
    }
};
