<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'field_status')) {
                $table->string('field_status', 32)->nullable()->after('booking_status');
            }
            if (!Schema::hasColumn('bookings', 'progress_percent')) {
                $table->unsignedTinyInteger('progress_percent')->nullable()->after('field_status');
            }
            if (!Schema::hasColumn('bookings', 'before_images')) {
                $table->json('before_images')->nullable()->after('evidence_photos');
            }
            if (!Schema::hasColumn('bookings', 'during_images')) {
                $table->json('during_images')->nullable()->after('before_images');
            }
            if (!Schema::hasColumn('bookings', 'after_images')) {
                $table->json('after_images')->nullable()->after('during_images');
            }
        });

        Schema::create('booking_execution_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id');
            $table->foreignUuid('user_id')->nullable();
            $table->text('note');
            $table->timestamps();

            $table->index('booking_id');
        });

        Schema::create('booking_support_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id');
            $table->foreignUuid('provider_id');
            $table->string('type', 32);
            $table->text('message')->nullable();
            $table->json('attachments')->nullable();
            $table->string('status', 32)->default('open');
            $table->timestamps();

            $table->index(['provider_id', 'status']);
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_support_requests');
        Schema::dropIfExists('booking_execution_notes');

        Schema::table('bookings', function (Blueprint $table) {
            foreach (['field_status', 'progress_percent', 'before_images', 'during_images', 'after_images'] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
