<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unpaid payment status removed — mark all existing bookings as paid.
     */
    public function up(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'is_paid')) {
            DB::table('bookings')->where('is_paid', 0)->update(['is_paid' => 1]);
        }

        if (Schema::hasTable('booking_repeats') && Schema::hasColumn('booking_repeats', 'is_paid')) {
            DB::table('booking_repeats')->where('is_paid', 0)->update(['is_paid' => 1]);
        }
    }

    public function down(): void
    {
        // Irreversible: previous unpaid state cannot be reconstructed safely.
    }
};
