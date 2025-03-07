<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Motomedialab\Checkout\Enums\OrderStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('checkout.tables.orders'), function (Blueprint $table) {
            $table->id();

            $table->uuid();

            $table->nullableMorphs('owner');

            $table->json('recipient_address');

            $table->string('currency')->default('GBP');
            $table->string('status', 20)->default(OrderStatus::PENDING->value);

            $table->unsignedBigInteger('amount_in_pence')->default(0);
            $table->unsignedBigInteger('discount_in_pence')->default(0);
            $table->unsignedBigInteger('shipping_in_pence')->default(0);

            $table->foreignId('voucher_id')->nullable()
                ->references('id')->on(config('checkout.tables.vouchers'))->nullOnDelete();

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
