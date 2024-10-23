<?php

namespace App\Http\Controllers;

use App\TON\Contracts\Wallets\Transfer;
use App\TON\Contracts\Wallets\TransferOptions;
use App\TON\Interop\Address;
use App\TON\Interop\Units;
use App\TON\Mnemonic\TonMnemonic;
use App\TON\Withdraws\WithdrawMemoToMemoInterface;
use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Illuminate\Http\Request;
use App\TON\Transports\Toncenter\ClientOptions;
use App\TON\Transports\Toncenter\ToncenterHttpV2Client;
use App\TON\Transports\Toncenter\ToncenterTransport;
use App\TON\Contracts\Wallets\V4\WalletV4R2;
use App\TON\Contracts\Wallets\V4\WalletV4Options;

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

    public function parseJetBody(Request $request)
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
        $phrases = config('services.ton.ton_mnemonic');
        $kp = TonMnemonic::mnemonicToKeyPair(explode(" ", $phrases));
        $options = new WalletV4Options($kp->publicKey);
        $wallet = new WalletV4R2($options);
        $seqno = (int)$wallet->seqno($tonTransport);
        $transfer = new TransferOptions($seqno);
        $extMsg = $wallet->createTransferMessage(
            [
                new Transfer(
                    new Address('0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k'),
                    Units::toNano("0.011"),
                    "sa v a",
                    \App\TON\SendMode::PAY_GAS_SEPARATELY,
                )
            ],
            $transfer
        );
        $tonTransport->sendMessage($extMsg, $kp->secretKey);
        return 1231312;
    }
}
