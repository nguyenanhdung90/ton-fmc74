<?php

namespace App\TON\Withdraws;

use App\Models\WalletTonMemo;
use App\TON\Exceptions\WithdrawTonException;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Interop\Units;
use App\TON\Transactions\TransactionHelper;
use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use App\TON\Transports\Toncenter\ClientOptions;
use App\TON\Transports\Toncenter\ToncenterHttpV2Client;
use App\TON\Transports\Toncenter\ToncenterTransport;

abstract class WithdrawAbstract
{
    abstract public function getWallet($pubicKey);

    protected function getBaseUri(): string
    {
        return config('services.ton.is_main') ? TonCenterClientInterface::MAIN_BASE_URI
            : TonCenterClientInterface::TEST_BASE_URI;
    }

    protected function getTonApiKey()
    {
        return config('services.ton.is_main') ? config('services.ton.api_key_main') :
            config('services.ton.api_key_test');
    }

    protected function getTransport(): ToncenterTransport
    {
        $httpClient = new HttpMethodsClient(
            Psr18ClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory(),
        );
        $tonCenter = new ToncenterHttpV2Client(
            $httpClient,
            new ClientOptions(
                $this->getBaseUri() . "api/v2",
                $this->getTonApiKey()
            )
        );
        return new ToncenterTransport($tonCenter);
    }

    /**
     * @throws WithdrawTonException
     */
    protected function isValidWalletTransferAmount(string $fromMemo, float $transferAmount, string $currency, int
    $decimals = Units::DEFAULT)
    {
        $walletMemo = WalletTonMemo::where('memo', $fromMemo)->where('currency', $currency)->first();
        if (!$walletMemo) {
            throw new WithdrawTonException("There is not memo account");
        }
        if ($walletMemo->amount < (string)Units::toNano($transferAmount, $decimals)) {
            throw new WithdrawTonException("Amount of wallet is not enough");
        }
    }
}
