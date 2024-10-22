<?php

namespace App\Http\Controllers;

use App\TON\Withdraws\WithdrawMemoToMemoInterface;
use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Illuminate\Http\Request;
use App\TON\Transports\Toncenter\ClientOptions;
use App\TON\Transports\Toncenter\ToncenterHttpV2Client;
use App\TON\Transports\Toncenter\ToncenterTransport;

class TonController extends Controller
{
    private WithdrawMemoToMemoInterface $withdrawMemoToMemo;

    public function __construct(
        WithdrawMemoToMemoInterface $withdrawMemoToMemo
    ) {
        $this->withdrawMemoToMemo = $withdrawMemoToMemo;
    }

    public function withdrawOnlyMemo(Request $request): string
    {
        $this->withdrawMemoToMemo->transfer('10', 'Usdt', 1, 'USDT');
        return 'Success';
    }

    public function parseJetBody(Request $request): array
    {
        $httpClient = new HttpMethodsClient(
            Psr18ClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory(),
        );
        $tonCenter = new ToncenterHttpV2Client(
            $httpClient,
            new ClientOptions(
                "https://testnet.toncenter.com/api/v2",
                "0e7ac59d0a2c5142ecaeb08d0497efc3da085744c6382371ff5711e6cbac428f"
            )
        );
        $tonTransport = new ToncenterTransport($tonCenter);


        $ko = 123;
    }
}
