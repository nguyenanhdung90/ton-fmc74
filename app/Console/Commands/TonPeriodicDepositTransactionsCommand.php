<?php

namespace App\Console\Commands;

use App\Jobs\InsertTonDepositTransaction;
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

    protected array $params;

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
        $this->params = ["limit" => null, "address" => config('services.ton.root_ton_wallet'), "to_lt" => null];
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $lastTransaction = WalletTonTransaction::whereNotNull('from_address_wallet')->orderBy('id', 'desc')->first();
        $toLt = $lastTransaction ? $lastTransaction->lt : 0;
        $limit = min($this->option('limit'), TransactionHelper::MAX_LIMIT_TRANSACTION);
        Arr::set($this->params, 'to_lt', $toLt);
        Arr::set($this->params, 'limit', $limit);
        while (true) {
            try {
                printf("Period transaction deposit query: %s \n", json_encode($this->params));
                sleep(5);
                $transactions = $this->tonCenterV2Client->getTransactionJsonRPC($this->params);
                $numberTx = $transactions->count();
                if (!$numberTx) {
                    printf("There are no transactions and continue after 5s ... \n");
                    continue;
                }

                $sources = $transactions->pluck('in_msg.source')->unique()->filter(function ($value, $key) {
                    return !empty($value);
                });
                $mapperSource = $this->mapperJetMasterByAddress->request($sources);

                printf("Processing %s transactions. \n", $numberTx);
                foreach ($transactions as $transaction) {
                    InsertTonDepositTransaction::dispatch($transaction, $mapperSource);
                }

                // set condition of query
                $lastTx = $transactions->first();
                Arr::set($this->params, 'to_lt', Arr::get($lastTx, 'transaction_id.lt'));
            } catch (\Exception $e) {
                printf($e->getMessage());
                continue;
            }
        }
        return Command::SUCCESS;
    }
}
