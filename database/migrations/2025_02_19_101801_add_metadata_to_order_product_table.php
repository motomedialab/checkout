<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(config('checkout.tables.order_product'), function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('vat_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('checkout.tables.order_product'), function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
