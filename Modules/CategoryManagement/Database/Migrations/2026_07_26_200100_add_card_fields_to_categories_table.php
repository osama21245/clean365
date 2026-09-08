<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'starting_price')) {
                $table->decimal('starting_price', 24, 2)->nullable()->after('description');
            }
            if (!Schema::hasColumn('categories', 'includes')) {
                $table->json('includes')->nullable()->after('starting_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'includes')) {
                $table->dropColumn('includes');
            }
            if (Schema::hasColumn('categories', 'starting_price')) {
                $table->dropColumn('starting_price');
            }
        });
    }
};
