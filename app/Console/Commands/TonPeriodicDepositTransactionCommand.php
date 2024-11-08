<?php

namespace App\Console\Commands;

use App\Models\WalletTonTransaction;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Jobs\TonSyncDepositTransaction;
use App\TON\Transactions\MapperJetMasterByAddressInterface;
use App\TON\TonHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class TonPeriodicDepositTransactionCommand extends Command
{
    /**
     * php artisan ton:periodic_deposit
     *
     * @var string
     */
    protected $signature = 'ton:periodic_deposit';

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
            "limit" => TonHelper::MAX_LIMIT_TRANSACTION,
            "address" => config('services.ton.root_wallet'),
            "to_lt" => null
        ];
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $lastTransaction = WalletTonTransaction::where('type', 'type', TonHelper::DEPOSIT)
            ->orderBy('lt', 'desc')->first();
        $toLt = $lastTransaction ? $lastTransaction->lt : null;
        Arr::set($this->params, 'to_lt', $toLt);
        while (true) {
            try {
                printf("Period transaction deposit query every 20s ...\n");
                sleep(20);
                $transactions = $this->tonCenterClient->getTransactionJsonRPC($this->params);
                $numberTx = $transactions->count();
                if (!$numberTx) {
                    continue;
                }

                $inMsgSources = $transactions->pluck('in_msg.source')->unique()->filter(function ($value, $key) {
                    return !empty($value);
                });
                $mapperSource = $this->mapperJetMasterByAddress->request($inMsgSources);

                printf("Check over %s transactions \n", $numberTx);
                foreach ($transactions as $transaction) {
                    TonSyncDepositTransaction::dispatch($transaction, $mapperSource);
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
