<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Motomedialab\Checkout\Enums\ProductStatus;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('checkout.tables.orders'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->json('recipient_address')->default('{}');
            
            $table->string('status', 20)->default(ProductStatus::AVAILABLE->value);
            
            $table->integer('total_exc_vat');
            $table->integer('vat');
            
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('checkout.tables.orders'));
    }
};
