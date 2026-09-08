<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Max-amount verification removed — mark all bookings as verified.
     */
    public function up(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'is_verified')) {
            DB::table('bookings')->where('is_verified', '!=', 1)->update(['is_verified' => 1]);
        }

        if (Schema::hasTable('booking_repeats') && Schema::hasColumn('booking_repeats', 'is_verified')) {
            DB::table('booking_repeats')->where('is_verified', '!=', 1)->update(['is_verified' => 1]);
        }
    }

    public function down(): void
    {
        // Irreversible.
    }
};
