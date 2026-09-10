<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('offer_banners')) {
            Schema::create('offer_banners', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('title', 191)->nullable();
                $table->text('subtitle')->nullable();
                $table->decimal('original_price', 10, 2)->nullable();
                $table->decimal('offer_price', 10, 2)->nullable();
                $table->string('tag', 191)->nullable();
                $table->string('discount_badge', 191)->nullable();
                $table->string('coupon_code', 191)->nullable();
                $table->string('resource_type', 191)->default('service');
                $table->foreignUuid('resource_id')->nullable();
                $table->string('redirect_link', 191)->nullable();
                $table->string('image')->nullable();
                $table->boolean('is_active')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer_banners');
    }
}
