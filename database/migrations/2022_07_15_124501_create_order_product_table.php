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
                ->references('id')->on('products')->nullOnDelete();
    
            $table->foreignId('order_id')->nullable()
                ->references('id')->on('products')->nullOnDelete();
            
            $table->unsignedInteger('quantity');
            $table->float('amount_in_pence');
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
