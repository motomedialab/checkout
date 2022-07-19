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
        Schema::create(config('checkout.tables.order_product'), function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('product_id')->nullable()
                ->references('id')->on(config('checkout.tables.products'))->nullOnDelete();
    
            $table->foreignId('order_id')->nullable()
                ->references('id')->on(config('checkout.tables.products'))->nullOnDelete();
            
            $table->unsignedInteger('quantity');
            
            $table->integer('amount_in_pence');
            $table->float('vat_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('checkout.tables.order_product'));
    }
};
