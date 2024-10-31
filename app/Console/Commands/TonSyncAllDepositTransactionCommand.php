<?php

namespace App\Console\Commands;

use App\Jobs\SyncTonDepositTransaction;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Transactions\MapperJetMasterByAddressInterface;
use App\TON\Transactions\TransactionHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class TonSyncAllDepositTransactionCommand extends Command
{
    /**
     * php artisan ton:sync_all_deposit
     *
     * @var string
     */
    protected $signature = 'ton:sync_all_deposit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected TonCenterClientInterface $tonCenterClient;

    protected MapperJetMasterByAddressInterface $mapperJetMasterByAddress;

    protected array $params;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        TonCenterClientInterface $tonCenterClient,
        MapperJetMasterByAddressInterface $mapperJetMasterByAddress
    ) {
        parent::__construct();
        $this->tonCenterClient = $tonCenterClient;
        $this->mapperJetMasterByAddress = $mapperJetMasterByAddress;
        $this->params = [
            "limit" => TransactionHelper::MAX_LIMIT_TRANSACTION,
            "address" => config('services.ton.root_ton_wallet'),
            "lt" => null,
            "hash" => null
        ];
    }

    public function handle(): int
    {
        while (true) {
            try {
                printf("New query transaction deposit : %s \n", json_encode(array_filter($this->params)));
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

                $sources = $transactions->pluck('in_msg.source')->unique()->filter(function ($value, $key) {
                    return !empty($value);
                });
                $mapperSource = $this->mapperJetMasterByAddress->request($sources);

                //printf("Processing %s transactions. \n", $numberTx);
                foreach ($transactions as $transaction) {
                    SyncTonDepositTransaction::dispatch($transaction, $mapperSource);
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
