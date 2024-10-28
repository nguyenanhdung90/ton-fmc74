<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TonPeriodicWithdrawExcessTransactionCommand extends Command
{
    /**
     * php artisan ton:periodic_withdraw_excess
     *
     * @var string
     */
    protected $signature = 'ton:periodic_withdraw_excess';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return 0;
    }
}
