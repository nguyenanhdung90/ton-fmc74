<?php

namespace App\Console\Commands;

use App\Jobs\InsertDepositTonTransaction;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Transactions\MapperJetMasterByAddressInterface;
use App\TON\Transactions\TransactionHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class TonDepositGetAllTransactionsCommand extends Command
{
    /**
     * php artisan ton:get_all_deposit
     *
     * @var string
     */
    protected $signature = 'ton:get_all_deposit {--limit=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected TonCenterClientInterface $tonCenterV2Client;

    protected MapperJetMasterByAddressInterface $mapperJetMasterByAddress;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        TonCenterClientInterface $tonCenterV2Client,
        MapperJetMasterByAddressInterface $mapperJetMasterByAddress
    ) {
        parent::__construct();
        $this->tonCenterV2Client = $tonCenterV2Client;
        $this->mapperJetMasterByAddress = $mapperJetMasterByAddress;
    }

    public function handle(): int
    {
        $limit = min($this->option('limit'), TransactionHelper::MAX_LIMIT_TRANSACTION);
        $params = ["limit" => $limit, "address" => config('services.ton.root_ton_wallet')];
        $lt = $hash = null;
        while (true) {
            try {
                if ($lt) {
                    Arr::set($params, 'lt', $lt);
                    Arr::set($params, 'hash', $hash);
                }
                printf("New query: %s \n", json_encode($params));
                sleep(1);
                $transactions = $this->tonCenterV2Client->getTransactionJsonRPC($params);
                $numberTx = $transactions->count();
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
                $mapperSource = $this->mapperJetMasterByAddress->process($sources);

                printf("Processing %s transactions. \n", $numberTx);
                foreach ($transactions as $transaction) {
                    InsertDepositTonTransaction::dispatch($transaction, $mapperSource);
                }

                // reset condition of query
                $lastTx = $transactions->last();
                $lt = Arr::get($lastTx, 'transaction_id.lt');
                $hash = Arr::get($lastTx, 'transaction_id.hash');
            } catch (\Exception $e) {
                printf($e->getMessage() . "\n");
                continue;
            }
        }
        return Command::SUCCESS;
    }
}
