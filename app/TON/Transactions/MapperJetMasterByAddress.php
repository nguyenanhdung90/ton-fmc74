<?php

namespace App\TON\Transactions;

use App\Exceptions\ErrorJettonMasterException;
use App\Exceptions\ErrorJettonWalletException;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Interop\Address;
use Illuminate\Support\Collection;

class MapperJetMasterByAddress implements MapperJetMasterByAddressInterface
{
    protected TonCenterClientInterface $tonCenterV2Client;

    public function __construct(TonCenterClientInterface $tonCenterV2Client)
    {
        $this->tonCenterV2Client = $tonCenterV2Client;
    }

    /**
     * @throws ErrorJettonWalletException
     * @throws ErrorJettonMasterException
     */
    public function request(Collection $address): Collection
    {
        $sourceChunks = $address->chunk(TransactionHelper::BATCH_NUMBER_JETTON_WALLET);
        $mapperSource = $this->parseMapperJetWallets($address);
        $jetWalletCollection = $this->getJetWallets($sourceChunks);
        if (!$jetWalletCollection) {
            throw new ErrorJettonWalletException("Error Jetton wallet: ",
                ErrorJettonWalletException::ERROR_JET_WALLET);
        }
        $this->setJetWalletToMapper($jetWalletCollection, $mapperSource);

        $jetWallets = $jetWalletCollection->pluck('jetton')->unique();
        $jetWalletChunks = $jetWallets->chunk(TransactionHelper::BATCH_NUMBER_JETTON_MASTER);
        $jetMasterCollections = $this->getJetMasters($jetWalletChunks);
        if (!$jetMasterCollections) {
            throw new ErrorJettonMasterException("Error Jetton master: ",
                ErrorJettonMasterException::ERROR_JET_MASTER);
        }
        $this->setJetMasterToMapper($jetMasterCollections, $mapperSource);
        return $mapperSource;
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
}
