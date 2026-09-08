<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'supervisor_seen_at')) {
                $table->timestamp('supervisor_seen_at')->nullable()->after('team_id');
            }
            if (!Schema::hasColumn('bookings', 'supervisor_seen_by')) {
                $table->foreignUuid('supervisor_seen_by')->nullable()->after('supervisor_seen_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            foreach (['supervisor_seen_by', 'supervisor_seen_at'] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
