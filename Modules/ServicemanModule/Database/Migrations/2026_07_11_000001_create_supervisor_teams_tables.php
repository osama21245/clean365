<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_id');
            $table->string('name', 191);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('supervisor_team_servicemen', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id');
            $table->foreignUuid('serviceman_id');
            $table->timestamps();

            $table->unique(['team_id', 'serviceman_id']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignUuid('team_id')->nullable()->after('serviceman_id');
        });

        Schema::create('booking_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id');
            $table->foreignUuid('provider_id');
            $table->foreignUuid('team_id')->nullable();
            $table->string('title', 191)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 32)->default('pending');
            $table->text('supervisor_notes')->nullable();
            $table->json('before_images')->nullable();
            $table->json('after_images')->nullable();
            $table->foreignUuid('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_task_attendees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_task_id');
            $table->foreignUuid('serviceman_id');
            $table->text('notes')->nullable();
            $table->json('before_images')->nullable();
            $table->json('after_images')->nullable();
            $table->boolean('is_attended')->default(false);
            $table->timestamp('attended_at')->nullable();
            $table->decimal('check_in_latitude', 17, 14)->nullable();
            $table->decimal('check_in_longitude', 17, 14)->nullable();
            $table->timestamps();

            $table->unique(['booking_task_id', 'serviceman_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_task_attendees');
        Schema::dropIfExists('booking_tasks');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('team_id');
        });

        Schema::dropIfExists('supervisor_team_servicemen');
        Schema::dropIfExists('supervisor_teams');
    }
};
