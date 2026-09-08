<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id');
            $table->foreignUuid('serviceman_id');
            $table->boolean('is_attended')->default(false);
            $table->timestamp('attended_at')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'serviceman_id']);
        });

        if (Schema::hasTable('booking_task_attendees') && Schema::hasTable('booking_tasks')) {
            $rows = DB::table('booking_task_attendees as a')
                ->join('booking_tasks as t', 't.id', '=', 'a.booking_task_id')
                ->select([
                    't.booking_id',
                    'a.serviceman_id',
                    DB::raw('MAX(CASE WHEN a.is_attended = 1 THEN 1 ELSE 0 END) as is_attended'),
                    DB::raw('MAX(a.attended_at) as attended_at'),
                ])
                ->groupBy('t.booking_id', 'a.serviceman_id')
                ->get();

            $now = now();
            foreach ($rows as $row) {
                DB::table('booking_attendances')->insert([
                    'id' => (string) Str::uuid(),
                    'booking_id' => $row->booking_id,
                    'serviceman_id' => $row->serviceman_id,
                    'is_attended' => (bool) $row->is_attended,
                    'attended_at' => $row->is_attended ? $row->attended_at : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Schema::dropIfExists('booking_task_attendees');
        Schema::dropIfExists('booking_tasks');
    }

    public function down(): void
    {
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

        Schema::dropIfExists('booking_attendances');
    }
};
