<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\TransactionModule\Entities\Transaction;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('short_code', 6)->nullable()->unique()->after('id');
        });

        $usedShortCodes = DB::table('transactions')
            ->whereNotNull('short_code')
            ->pluck('short_code')
            ->flip()
            ->all();

        DB::table('transactions')
            ->select('id')
            ->whereNull('short_code')
            ->orderBy('created_at')
            ->orderBy('id')
            ->cursor()
            ->each(function ($transaction) use (&$usedShortCodes) {
                $shortCode = $this->generateUniqueShortCode($usedShortCodes);

                DB::table('transactions')
                    ->where('id', $transaction->id)
                    ->update(['short_code' => $shortCode]);
            });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('short_code');
        });
    }

    private function generateUniqueShortCode(array &$usedShortCodes): string
    {
        for ($attempt = 0; $attempt < 50; $attempt++) {
            $shortCode = Transaction::generateUniqueShortCode();

            if (!isset($usedShortCodes[$shortCode])) {
                $usedShortCodes[$shortCode] = true;

                return $shortCode;
            }
        }

        throw new RuntimeException('Unable to backfill transaction short codes.');
    }
};
