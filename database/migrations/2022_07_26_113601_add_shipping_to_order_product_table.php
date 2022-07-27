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
        Schema::table(config('checkout.tables.order_product'), function (Blueprint $table) {
            $table->unsignedInteger('shipping_in_pence')->default(0)->after('amount_in_pence');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('checkout.tables.order_product'), function (Blueprint $table) {
            $table->dropColumn('shipping_in_pence');
        });
    }
};
