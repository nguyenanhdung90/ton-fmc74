<?php

namespace App\Console\Commands;

use App\Jobs\TonSyncExcessTransaction;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Transactions\TransactionHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class TonSyncAllWithdrawExcessTransactionCommand extends Command
{
    /**
     * php artisan ton:sync_all_excess
     *
     * @var string
     */
    protected $signature = 'ton:sync_all_excess';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sync all transaction excess withdraw';

    protected TonCenterClientInterface $tonCenterClient;

    protected array $params;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        TonCenterClientInterface $tonCenterClient
    ) {
        parent::__construct();
        $this->tonCenterClient = $tonCenterClient;
        $this->params = [
            "limit" => TransactionHelper::MAX_LIMIT_TRANSACTION,
            "address" => config('services.ton.root_ton_wallet'),
            "lt" => null,
            "hash" => null
        ];
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        while (true) {
            try {
                printf("New query transaction excess withdraw : %s \n", json_encode(array_filter($this->params)));
                sleep(1);
                $transactions = $this->tonCenterClient->getTransactionJsonRPC($this->params);
                $numberTx = $transactions->count();
                $lt = Arr::get($this->params, 'lt');
                if (!$lt && !$numberTx) {
                    printf("End, there are no transactions for the first loop \n");
                    break;
                }
                if ($lt && $numberTx == 1) {
                    printf("End, there are no transactions for the next loop \n");
                    break;
                }
                printf("Check over %s transactions \n", $numberTx);
                foreach ($transactions as $transaction) {
                    if (empty(Arr::get($transaction, 'out_msgs'))) {
                        TonSyncExcessTransaction::dispatch($transaction);
                    }
                }
                // set condition of query
                $lastTx = $transactions->last();
                Arr::set($this->params, 'lt', Arr::get($lastTx, 'transaction_id.lt'));
                Arr::set($this->params, 'hash', Arr::get($lastTx, 'transaction_id.hash'));
            } catch (\Exception $e) {
                printf($e->getMessage() . "\n");
                continue;
            }
        }
        return Command::SUCCESS;
    }
}
