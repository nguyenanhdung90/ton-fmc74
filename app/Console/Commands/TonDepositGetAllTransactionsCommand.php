<?php

namespace App\Console\Commands;

use App\Jobs\InsertDepositTonTransaction;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Interop\Address;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TonDepositGetAllTransactionsCommand extends Command
{
    const BATCH_NUMBER_JETTON_WALLET = 2;

    const BATCH_NUMBER_JETTON_MASTER = 1;

    const MAX_LIMIT_TRANSACTION = 100;
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
        printf("Start get all transaction deposit ... \n");
        $limit = min($this->option('limit'), self::MAX_LIMIT_TRANSACTION);
        $params = [
            "limit" => $limit,
            "address" => config('services.ton.root_ton_wallet'),
        ];
        $lt = $hash = null;
        while (true) {
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

            // get mapper jetton master
            $sources = $transactions->pluck('in_msg.source')->unique()->filter(function ($value, $key) {
                return !empty($value);
            });

            $sourceChunks = $sources->chunk(self::BATCH_NUMBER_JETTON_WALLET);
            $mapperSource = $this->parseMapperJetWallets($sources);

            $jetWalletCollection = $this->getJetWallets($sourceChunks);
            if (!$jetWalletCollection) {
                printf("Error when get jetton wallet by query: %s \n", json_encode($params));
                continue;
            }
            $this->setJetWalletToMapper($jetWalletCollection, $mapperSource);

            $jetWallets = $jetWalletCollection->pluck('jetton')->unique();
            $jetWalletChunks = $jetWallets->chunk(self::BATCH_NUMBER_JETTON_MASTER);
            $jetMasterCollections = $this->getJetMasters($jetWalletChunks);
            if (is_null($jetMasterCollections)) {
                printf("Error when get jetton master by query: %s \n", json_encode($params));
                sleep(1);
                continue;
            }
            $this->setJetMasterToMapper($jetMasterCollections, $mapperSource);
            // end get mapper jettton master

            printf("Processing %s transactions. \n", $numberTx);
            $this->processTx($transactions, $mapperSource);

            // reset condition of query
            $lastTx = $transactions->last();
            $lt = Arr::get($lastTx, 'transaction_id.lt');
            $hash = Arr::get($lastTx, 'transaction_id.hash');
        }
        return 1;
    }

    private function getJetWallets(Collection $sourceChunks): ?Collection
    {
        $mergedJetWallet = collect([]);
        foreach ($sourceChunks as $sourceChunk) {
            $params = [
                "limit" => $sourceChunk->count(),
                "address" => $sourceChunk->implode(','),
            ];
            sleep(1);
            $jetWallet = $this->tonCenterV2Client->getJetWallets($params);
            if (!$jetWallet) {
                return null;
            }
            $mergedJetWallet = $mergedJetWallet->merge($jetWallet);
        }
        return $mergedJetWallet;
    }

    private function getJetMasters(Collection $jetWalletChunks): ?Collection
    {
        $mergedJetMaster = collect([]);
        foreach ($jetWalletChunks as $jetWalletChunk) {
            $params = [
                "limit" => $jetWalletChunk->count(),
                "address" => $jetWalletChunk->implode(',')
            ];
            sleep(1);
            $jetMaster = $this->tonCenterV2Client->getJetMasters($params);
            if (!$jetMaster) {
                return null;
            }
            $mergedJetMaster = $mergedJetMaster->merge($jetMaster);
        }
        return $mergedJetMaster;
    }

    private function parseMapperJetWallets(Collection $sources): Collection
    {
        $sources->transform(function ($item, $key) {
            $address = new Address($item);
            return [
                'source' => $item,
                'hex' => strtoupper($address->toString(false)),
                'jetton_wallet' => null,
                'jetton_master' => []
            ];
        });
        return $sources->keyBy('source');
    }

    private function setJetWalletToMapper(Collection $jetWalletCollection, &$mappers)
    {
        $keyIndexJetWallets = $jetWalletCollection->keyBy('address');
        $mappers->transform(function ($item, $key) use ($keyIndexJetWallets) {
            $hex = $item['hex'];
            if ($keyIndexJetWallets->has($hex)) {
                $item['jetton_wallet'] = $keyIndexJetWallets->get($hex)['jetton'];
            }
            return $item;
        });
    }

    private function setJetMasterToMapper(Collection $jetMasterCollection, &$mappers)
    {
        $keyIndexJetMasters = $jetMasterCollection->keyBy('address');
        $mappers->transform(function ($item, $key) use ($keyIndexJetMasters) {
            if (empty($item['jetton_wallet'])) {
                return $item;
            }
            $indexHex = $item['jetton_wallet'];
            if ($keyIndexJetMasters->has($indexHex)) {
                $item['jetton_master'] = $keyIndexJetMasters->get($indexHex)['jetton_content'];
            }
            return $item;
        });
    }

    private function processTx(Collection $transactions, Collection $mapperSource)
    {
        foreach ($transactions as $transaction) {
            $source = Arr::get($transaction, 'in_msg.source');
            if (!empty($source) && $mapperSource->has($source)) {
                Arr::set($transaction, 'in_msg.source_details', $mapperSource->get($source));
            }
            InsertDepositTonTransaction::dispatch($transaction);
        }
    }
}
