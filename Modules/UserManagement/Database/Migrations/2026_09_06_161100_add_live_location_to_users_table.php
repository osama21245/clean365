<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'latitude')) {
                $table->string('latitude', 50)->nullable()->after('fcm_token');
            }
            if (!Schema::hasColumn('users', 'longitude')) {
                $table->string('longitude', 50)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('users', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_seen_at')) {
                $table->dropColumn('last_seen_at');
            }
            if (Schema::hasColumn('users', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('users', 'latitude')) {
                $table->dropColumn('latitude');
            }
        });
    }
};
