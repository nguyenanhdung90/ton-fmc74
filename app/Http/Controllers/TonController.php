<?php

namespace App\Http\Controllers;

use App\TON\Contracts\Jetton\JettonMinter;
use App\TON\Contracts\Jetton\JettonWallet;
use App\TON\Contracts\Jetton\JettonWalletOptions;
use App\TON\Contracts\Jetton\TransferJettonOptions;
use App\TON\Contracts\Wallets\Transfer;
use App\TON\Contracts\Wallets\TransferOptions;
use App\TON\Interop\Address;
use App\TON\Interop\Boc\SnakeString;
use App\TON\Interop\Units;
use App\TON\Mnemonic\TonMnemonic;
use App\TON\SendMode;
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

    /**
     * @throws \App\TON\Interop\Boc\Exceptions\BitStringException
     * @throws \App\TON\Mnemonic\Exceptions\TonMnemonicException
     * @throws \App\TON\Contracts\Exceptions\ContractException
     * @throws \App\TON\Contracts\Wallets\Exceptions\WalletException
     * @throws \App\TON\Exceptions\TransportException
     */
    public function withdrawUSDT(Request $request): int
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
        $transport = new ToncenterTransport($tonCenter);
        $phrases = config('services.ton.ton_mnemonic');
        $kp = TonMnemonic::mnemonicToKeyPair(explode(" ", $phrases));
        $options = new WalletV4Options($kp->publicKey);
        $wallet = new WalletV4R2($options);
        /** @var Address $walletAddress */
        $walletAddress = $wallet->getAddress();
        $usdtRoot = JettonMinter::fromAddress(
            $transport,
            new Address(config('services.ton.root_usdt_test'))
        );
        $usdtWalletAddress = $usdtRoot->getJettonWalletAddress($transport, $walletAddress);
        $usdtWallet = new JettonWallet(new JettonWalletOptions(
            null, 0, $usdtWalletAddress
        ));
        $seqno = (int)$wallet->seqno($transport);
        $transfer = new TransferOptions($seqno);
        $extMessage = $wallet->createTransferMessage([
            new Transfer(
                $usdtWalletAddress,
                Units::toNano("0.1"),
                $usdtWallet->createTransferBody(
                    new TransferJettonOptions(
                        Units::toNano("0.03", Units::USDt),
                        new Address('0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k'),
                        $walletAddress,
                        0,
                        SnakeString::fromString("holaa")->cell(true),
                        Units::toNano("0.0000001")
                    )
                ),
                SendMode::combine([SendMode::PAY_GAS_SEPARATELY, SendMode::CARRY_ALL_REMAINING_INCOMING_VALUE,
                    SendMode::IGNORE_ERRORS]),
            )],
            $transfer
        );
        $transport->sendMessage($extMessage, $kp->secretKey);
        return 9999;
    }
}
