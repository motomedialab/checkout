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
        Schema::table(config('checkout.tables.orders'), function (Blueprint $table) {
            $table->float('vat_rate')->default(0)->after('shipping_in_pence');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('checkout.tables.orders'), function (Blueprint $table) {
            $table->dropColumn('vat_rate');
        });
    }
};
