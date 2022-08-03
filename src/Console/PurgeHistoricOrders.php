<?php

namespace Motomedialab\Checkout\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Motomedialab\Checkout\Enums\OrderStatus;

class PurgeHistoricOrders extends Command
{
    protected $signature = 'orders:purge';
    
    protected $description = 'Purges all pending orders older than 90 days';
    
    public function handle()
    {
        $count = DB::table(config('checkout.table.orders'))
            ->where('status', OrderStatus::PENDING->value)
            ->where('updated_at', '<=', now()->subDays(90))
            ->delete();
        
        $this->info("Purged $count historic orders.");
    }
}