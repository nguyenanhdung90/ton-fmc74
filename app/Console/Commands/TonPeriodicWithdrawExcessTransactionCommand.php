<?php

namespace App\Console\Commands;

use App\Models\WalletTonTransaction;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Jobs\TonSyncExcessTransaction;
use App\TON\TonHelper;
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
     * this transaction of TON network is notify for jetton withdraw
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
        $this->params = ["limit" => TonHelper::MAX_LIMIT_TRANSACTION,
            "address" => config('services.ton.root_wallet'),
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
        $lastTransaction = WalletTonTransaction::where('type', TonHelper::WITHDRAW_EXCESS)
            ->orderBy('lt', 'desc')
            ->first();
        $toLt = $lastTransaction ? $lastTransaction->lt : 0;
        Arr::set($this->params, 'to_lt', $toLt);
        while (true) {
            printf("Period transaction withdraw excess query every 20 ...\n");
            sleep(20);
            $transactions = $this->tonCenterClient->getTransactionJsonRPC($this->params);
            $numberTx = $transactions->count();
            if (!$numberTx) {
                continue;
            }
            printf("Check over %s transactions \n", $numberTx);
            foreach ($transactions as $transaction) {
                if (empty(Arr::get($transaction, 'out_msgs'))) {
                    TonSyncExcessTransaction::dispatch($transaction);
                }
            }
            // set condition of query
            $lastTx = $transactions->first();
            Arr::set($this->params, 'to_lt', Arr::get($lastTx, 'transaction_id.lt'));
        }
        return Command::SUCCESS;
    }
}
