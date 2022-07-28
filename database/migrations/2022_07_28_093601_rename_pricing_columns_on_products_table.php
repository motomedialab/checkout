<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected string $table;
    
    public function __construct()
    {
        $this->table = config('checkout.tables.products');
    }
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `$this->table` RENAME COLUMN `pricing` TO `pricing_in_pence`");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `$this->table` RENAME COLUMN `shipping` TO `shipping_in_pence`");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `$this->table` RENAME COLUMN `pricing_in_pence` TO `pricing`");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `$this->table` RENAME COLUMN `shipping_in_pence` TO `shipping`");
    }
};
