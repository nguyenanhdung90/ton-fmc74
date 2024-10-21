<?php

namespace App\Console\Commands;

use App\Jobs\InsertDepositTonTransaction;
use App\Models\WalletTonTransaction;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Transactions\MapperJetMasterByAddressInterface;
use App\TON\Transactions\TransactionHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class TonPeriodicDepositTransactionsCommand extends Command
{
    /**
     * php artisan ton:periodic_deposit
     *
     * @var string
     */
    protected $signature = 'ton:periodic_deposit {--limit=100}';

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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $limit = min($this->option('limit'), TransactionHelper::MAX_LIMIT_TRANSACTION);
        $lastTransaction = WalletTonTransaction::whereNotNull('from_address_wallet')->orderBy('id', 'desc')->first();
        $toLt = $lastTransaction ? $lastTransaction->lt : 0;
        $params = ["limit" => $limit, "address" => config('services.ton.root_ton_wallet')];

        while (true) {
            try {
                if ($toLt) {
                    Arr::set($params, 'to_lt', $toLt);
                }
                printf("Period transaction deposit query: %s \n", json_encode($params));
                sleep(5);//5
                $transactions = $this->tonCenterV2Client->getTransactionJsonRPC($params);
                $numberTx = $transactions->count();
                if (!$numberTx) {
                    printf("There are no transactions and continue after 5s ... \n");
                    continue;
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
                $lastTx = $transactions->first();
                $toLt = Arr::get($lastTx, 'transaction_id.lt');
            } catch (\Exception $e) {
                printf($e->getMessage());
                continue;
            }
        }
        return Command::SUCCESS;
    }
}
