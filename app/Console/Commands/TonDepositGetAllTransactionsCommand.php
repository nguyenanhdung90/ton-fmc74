<?php

namespace App\Console\Commands;

use App\Jobs\InsertDepositTonTransaction;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Interop\Address;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class TonDepositGetAllTransactionsCommand extends Command
{
    const BATCH_NUMBER_JETTON_WALLET = 2;

    const BATCH_NUMBER_JETTON_MASTER = 1;
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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TonCenterClientInterface $tonCenterV2Client)
    {
        parent::__construct();
        $this->tonCenterV2Client = $tonCenterV2Client;
    }

    public function handle(): int
    {
        echo "Start get all transaction deposit ... \n";
        $limit = min($this->option('limit'), 100);
        $transactionQuery = [
            "method" => "getTransactions",
            "params" => [
                "limit" => $limit,
                "address" => config('services.ton.root_ton_wallet'),
                "archival" => true
            ],
        ];

        $lt = $hash = null;
        while (true) {
            if ($lt) {
                Arr::set($transactionQuery, 'params.lt', $lt);
                Arr::set($transactionQuery, 'params.hash', $hash);
            }

            printf("Query: %s \n" , json_encode($transactionQuery['params']));
            sleep(1);
            $data = $this->tonCenterV2Client->jsonRPC($transactionQuery);
            $ok = Arr::get($data, 'ok');
            if (!$ok) {
                printf("Error from TonCenter: %s \n", json_encode($data));
                break;
            }
            $transactions = Arr::get($data, 'result');
            $numberTx = count($transactions);
            if (is_null($lt) && !$numberTx) {
                printf("End, there are no transactions for the first loop \n");
                break;
            }
            if (!is_null($lt) && $numberTx == 1) {
                printf("End, there are no transactions for the next loop \n");
                break;
            }

            // get mapper jettton master
            $sources = array_unique(array_filter(Arr::pluck($transactions, 'in_msg.source')));
            $batchSources = $this->getBatches($sources, self::BATCH_NUMBER_JETTON_WALLET);
            $jetWallets = $this->getBatchJetWallets($batchSources);
            if (is_null($jetWallets)) {
                printf("Error when get jetton wallet by query: %s \n", json_encode($transactionQuery['params']));
                continue;
            }
            $mapperJetWallets = $this->parseMapperJetWallets($sources);
            $this->setJetWalletToMapper($mapperJetWallets, $jetWallets);

            $hexJetWallets = array_unique(Arr::pluck($jetWallets, 'jetton'));
            $batchJetWallets = $this->getBatches($hexJetWallets, self::BATCH_NUMBER_JETTON_MASTER);
            $jetMasters = $this->getBatchJetMasters($batchJetWallets);
            if (is_null($jetMasters)) {
                printf("Error when get jetton master by query: %s \n", json_encode($transactionQuery['params']));
                sleep(1);
                continue;
            }
            $this->setJetMasterToMapper($mapperJetWallets, $jetMasters);
            // end get mapper jettton master

            printf("Processing %s transactions. \n", $numberTx);
            $this->processTx($transactions, $mapperJetWallets);

            // reset condition of query
            $lastTx = end($transactions);
            $lt = Arr::get($lastTx, 'transaction_id.lt');
            $hash = Arr::get($lastTx, 'transaction_id.hash');
        }
        return 1;
    }

    private function getBatches(array $sources, int $length): array
    {
        $groupSources = [];
        $numberSources = count($sources);
        $numberGroup = ceil($numberSources / $length);
        for ($x = 0; $x < $numberGroup; $x++) {
            $groupSources[] = array_slice($sources, $x * self::BATCH_NUMBER_JETTON_WALLET, self::BATCH_NUMBER_JETTON_WALLET);
        }
        return $groupSources;
    }

    private function getBatchJetWallets(array $batchSources)
    {

        $jetWallets = [];
        foreach ($batchSources as $sources) {
            sleep(2);
            $params = [
                "limit" => count($sources),
                "address" => implode(',', $sources),
            ];
            sleep(1);
            $results = $this->tonCenterV2Client->getJettonWallets($params);
            //echo json_encode($results);
            if (!$results['ok']) {
                //echo json_encode($results);
                return;
            }
            if (!empty($results['data']['jetton_wallets'])) {
                $jetWallets = array_merge($jetWallets, $results['data']['jetton_wallets']);
            }
        }
        return $jetWallets;
    }

    private function getBatchJetMasters(array $batchJetWallets)
    {

        $jetMasters = [];
        foreach ($batchJetWallets as $jetWallets) {
            $params = [
                "limit" => count($jetWallets),
                "address" => implode(',', $jetWallets)
            ];
            sleep(1);
            $results = $this->tonCenterV2Client->getJettonMasters($params);
            if (!$results['ok']) {
                return;
            }
            if (!empty($results['data']['jetton_masters'])) {
                $jetMasters = array_merge($jetMasters, $results['data']['jetton_masters']);
            }
        }
        return $jetMasters;
    }

    private function parseMapperJetWallets(array $sources): array
    {
        $mappers = [];
        foreach ($sources as $source) {
            $address = new Address($source);
            $mappers[$source] = [
                'hex' => strtolower($address->toString(false)),
                'jetton_wallet' => null,
                'jetton_master' => []
            ];
        }
        return $mappers;
    }

    private function setJetWalletToMapper(&$mappers, array $jetWallets)
    {
        $keyIndexJetWallets = [];
        foreach ($jetWallets as $item) {
            $indexHex = strtolower($item['address']);
            $keyIndexJetWallets[$indexHex]['jetton'] = Arr::get($item, 'jetton');
        }
        foreach ($mappers as $key => $item) {
            $indexHex = $item['hex'];
            if (isset($keyIndexJetWallets[$indexHex])) {
                $mappers[$key]['jetton_wallet'] = strtolower(Arr::get($keyIndexJetWallets[$indexHex], 'jetton'));
            }
        }
    }

    private function setJetMasterToMapper(&$mappers, array $jetMasters)
    {
        $keyIndexJetMasters = [];
        foreach ($jetMasters as $item) {
            $indexHex = strtolower($item['address']);
            $keyIndexJetMasters[$indexHex]['jetton_content'] = Arr::get($item, 'jetton_content');
        }
        foreach ($mappers as $key => $item) {
            if (empty($item['jetton_wallet'])) {
                continue;
            }
            $indexHex = $item['jetton_wallet'];
            if (isset($keyIndexJetMasters[$indexHex])) {
                $mappers[$key]['jetton_master'] = Arr::get($keyIndexJetMasters[$indexHex], 'jetton_content');
            }
        }
    }

    private function processTx(array $transactions, array $mapperJetWallets)
    {
        foreach ($transactions as $transaction) {
            $source = Arr::get($transaction, 'in_msg.source');
            if (!empty($source) && isset($mapperJetWallets[$source])) {
                Arr::set($transaction, 'in_msg.source_details', $mapperJetWallets[$source]);
            }
            InsertDepositTonTransaction::dispatch($transaction);
        }
    }
}
