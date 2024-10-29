<?php

namespace App\Console\Commands;

use App\Jobs\SyncTonExcessTransaction;
use App\Models\WalletTonTransaction;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Transactions\TransactionHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class TonPeriodicWithdrawExcessTransactionCommand extends Command
{
    /**
     * php artisan ton:periodic_withdraw_excess
     *
     * @var string
     */
    protected $signature = 'ton:periodic_withdraw_excess';

    /**
     * Sync transaction excess by query id for withdraw
     *
     * @var string
     */
    protected $description = 'Sync transaction excess by query id for withdraw';

    protected TonCenterClientInterface $tonCenterClient;

    protected array $params;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TonCenterClientInterface $tonCenterClient)
    {
        $this->tonCenterClient = $tonCenterClient;
        $this->params = ["limit" => TransactionHelper::MAX_LIMIT_TRANSACTION,
            "address" => config('services.ton.root_ton_wallet'),
            "to_lt" => null];
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $lastTransaction = WalletTonTransaction::where('type', TransactionHelper::WITHDRAW_EXCESS)
            ->orderBy('id', 'desc')
            ->first();
        $toLt = $lastTransaction ? $lastTransaction->lt : 0;
        Arr::set($this->params, 'to_lt', $toLt);
        while (true) {
            printf("Period transaction excess query: %s \n", json_encode($this->params));
            sleep(5);
            $transactions = $this->tonCenterClient->getTransactionJsonRPC($this->params);
            $numberTx = $transactions->count();
            if (!$numberTx) {
                printf("There are no transactions and continue after 5s ... \n");
                continue;
            }
            foreach ($transactions as $transaction) {
                if (empty(Arr::get($transaction, 'out_msgs'))) {
                    SyncTonExcessTransaction::dispatch($transaction);
                }
            }
            // set condition of query
            $lastTx = $transactions->first();
            Arr::set($this->params, 'to_lt', Arr::get($lastTx, 'transaction_id.lt'));
        }
        return Command::SUCCESS;
    }
}
