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
        Schema::create(config('checkout.tables.vouchers'), function (Blueprint $table) {
            $table->id();
            
            $table->string('code');
            
            $table->integer('max_uses')->default(0);
            $table->integer('total_uses')->default(0);
            
            $table->dateTime('valid_from')->nullable()->default(null);
            $table->dateTime('valid_until')->nullable()->default(null);
            
            // true is percentage, false is fixed value
            $table->boolean('percentage')->default(false);
            
            // true is apply against a product, false is against a basket
            $table->boolean('on_product')->default(true);
            
            // true is apply to the total quantity, false is apply to one item
            $table->boolean('quantity_price')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('checkout.tables.vouchers'));
    }
};
