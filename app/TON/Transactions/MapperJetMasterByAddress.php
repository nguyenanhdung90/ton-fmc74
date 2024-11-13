<?php

namespace App\TON\Transactions;

use App\TON\Exceptions\ErrorJettonWalletException;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Interop\Address;
use App\TON\TonHelper;
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
    public function request(Collection $inMsgSources): Collection
    {
        $mapperInMsgSource = $this->transformMapperJetWallets($inMsgSources);
        $inMsgSourceChunks = $inMsgSources->chunk(TonHelper::BATCH_NUMBER_JETTON_WALLET);
        $jetWalletCollection = $this->getJetWallets($inMsgSourceChunks);
        if (!$jetWalletCollection) {
            throw new ErrorJettonWalletException("Error when get Jetton wallets \n");
        }
        $this->setJetWalletToMapper($jetWalletCollection, $mapperInMsgSource);
        return $mapperInMsgSource;
    }

    private function transformMapperJetWallets(Collection $inMsgSources): Collection
    {
        $inMsgSources->transform(function ($source, $key) {
            $address = new Address($source);
            return [
                'in_msg_source' => $source,
                'hex_source' => strtoupper($address->toString(false)),
                'jetton_master' => []
            ];
        });
        return $inMsgSources->keyBy('in_msg_source');
    }

    private function getJetWallets(Collection $inMsgSourceChunks): ?Collection
    {
        $mergedJetWallet = collect([]);
        foreach ($inMsgSourceChunks as $sourceChunk) {
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

    private function setJetWalletToMapper(Collection $jetWalletCollection, &$mapperInMsgSource)
    {
        $keyIndexJetWallets = $jetWalletCollection->keyBy('address');
        $mapperInMsgSource->transform(function ($item, $key) use ($keyIndexJetWallets) {
            $hexSource = $item['hex_source'];
            if ($keyIndexJetWallets->has($hexSource)) {
                $hexAddressJettonMaster = strtoupper($keyIndexJetWallets->get($hexSource)['jetton']);
                $item['jetton_master'] = TonHelper::getJettonAttribute($hexAddressJettonMaster);
            }
            return $item;
        });
    }
}
