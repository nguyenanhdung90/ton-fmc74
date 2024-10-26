<?php

namespace App\Console\Commands;

use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TonPeriodicWithdrawJettonTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ton:periodic_withdraw_jetton';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'periodic sync withdraw transaction only for Jetton';

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
     * @param TonCenterClientInterface $tonCenterClient
     * @return int
     */
    public function handle(TonCenterClientInterface $tonCenterClient): int
    {
        while (true) {
            try {
                printf("Period transaction withdraw jetton query every 5s ...\n");
                sleep(5);
                $withDrawTransactions = DB::table('wallet_ton_transactions')
                    ->where('type', TransactionHelper::WITHDRAW)
                    ->where('currency', '!=', TransactionHelper::TON)
                    ->whereDate('created_at', '<=', Carbon::now()->subSeconds(5))
                    ->whereNull('lt')->limit(TransactionHelper::MAX_LIMIT_TRANSACTION)->get();
                if (!$withDrawTransactions->count()) {
                    continue;
                }
                printf("Processing %s withdraw transactions. \n", $withDrawTransactions->count());
                foreach ($withDrawTransactions as $withdrawTx) {
                    sleep(2);
                    $msgHash = $withdrawTx->hash;
                }
            } catch (\Exception $e) {
                printf("Exception periodic withdraw jetton: " . $e->getMessage());
                continue;
            }
        }
        return Command::SUCCESS;
    }
}
