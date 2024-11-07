<?php

namespace App\TON\Transactions;

use App\Exceptions\ErrorJettonWalletException;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Interop\Address;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MapperJetMasterByAddress implements MapperJetMasterByAddressInterface
{
    protected TonCenterClientInterface $tonCenterClient;

    public function __construct(TonCenterClientInterface $tonCenterClient)
    {
        $this->tonCenterClient = $tonCenterClient;
    }

    /**
     * @throws ErrorJettonWalletException
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
        return $mapperSource;
    }

    private function parseMapperJetWallets(Collection $sources): Collection
    {
        $sources->transform(function ($item, $key) {
            $address = new Address($item);
            return [
                'source' => $item,
                'hex' => strtoupper($address->toString(false)),
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
            $jetWallet = $this->tonCenterClient->getJetWallets($params);
            if (!$jetWallet) {
                return null;
            }
            $mergedJetWallet = $mergedJetWallet->merge($jetWallet);
        }
        return $mergedJetWallet;
    }

    private function setJetWalletToMapper(Collection $jetWalletCollection, &$mappers)
    {
        $mappersJettonAttribute = TransactionHelper::validJettonAttribute();
        $keyIndexJetWallets = $jetWalletCollection->keyBy('address');
        $mappers->transform(function ($item, $key) use ($keyIndexJetWallets, $mappersJettonAttribute) {
            $hex = $item['hex'];
            if ($keyIndexJetWallets->has($hex)) {
                $jettonMasterAddressHex = strtolower($keyIndexJetWallets->get($hex)['jetton']);
                $item['jetton_master'] = Arr::has($mappersJettonAttribute, $jettonMasterAddressHex) ?
                    Arr::get($mappersJettonAttribute, $jettonMasterAddressHex) :
                    TransactionHelper::NONSUPPORT_JETTON;
            }
            return $item;
        });
    }
}
