<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('checkout.tables.product_voucher'), function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('product_id')->nullable()
                ->references('id')->on(config('checkout.tables.products'))->nullOnDelete();
    
            $table->foreignId('voucher_id')->nullable()
                ->references('id')->on(config('checkout.tables.vouchers'))->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('checkout.tables.product_voucher'));
    }
};
