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
        Schema::create(config('checkout.tables.products'), function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('parent_product_id')->nullable()
                ->references('id')->on(config('checkout.tables.products'))
                ->cascadeOnDelete();
            
            $table->string('status', 20)->default(ProductStatus::AVAILABLE->value);
            $table->string('name');
            
            $table->float('vat_rate')->default(0);
            
            $table->json('pricing')->default('[]');
            $table->json('shipping')->default('[]');
            
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
        Schema::dropIfExists(config('checkout.tables.products'));
    }
};
