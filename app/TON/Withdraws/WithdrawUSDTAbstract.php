<?php

namespace App\TON\Withdraws;

use App\Jobs\InsertWithdrawTonTransaction;
use App\TON\Contracts\Exceptions\ContractException;
use App\TON\Contracts\Jetton\JettonMinter;
use App\TON\Contracts\Jetton\JettonWallet;
use App\TON\Contracts\Jetton\JettonWalletOptions;
use App\TON\Contracts\Jetton\TransferJettonOptions;
use App\TON\Contracts\Wallets\Transfer;
use App\TON\Contracts\Wallets\TransferOptions;
use App\TON\Exceptions\TransportException;
use App\TON\Interop\Address;
use App\TON\Interop\Boc\Exceptions\BitStringException;
use App\TON\Interop\Boc\SnakeString;
use App\TON\Interop\Units;
use App\TON\Mnemonic\Exceptions\TonMnemonicException;
use App\TON\Mnemonic\TonMnemonic;
use App\TON\SendMode;
use App\TON\Transactions\TransactionHelper;

abstract class WithdrawUSDTAbstract extends WithdrawAbstract
{
    protected function getRootUSDT()
    {
        return config('services.ton.is_main') ? config('services.ton.root_usdt_main') :
            config('services.ton.root_usdt_test');
    }


    /**
     * @throws BitStringException
     * @throws TonMnemonicException
     * @throws ContractException
     * @throws TransportException
     */
    public function process(string $fromMemo, string $destAddress, string $transferAmount, string $toMemo = "")
    {
        $phrases = config('services.ton.ton_mnemonic');
        $kp = TonMnemonic::mnemonicToKeyPair(explode(" ", $phrases));
        $wallet = $this->getWallet($kp->publicKey);
        /** @var Address $walletAddress */
        $walletAddress = $wallet->getAddress();
        $transport = $this->getTransport();
        $usdtRoot = JettonMinter::fromAddress(
            $transport,
            new Address($this->getRootUSDT())
        );
        $usdtWalletAddress = $usdtRoot->getJettonWalletAddress($transport, $walletAddress);
        $usdtWallet = new JettonWallet(new JettonWalletOptions(
            null, 0, $usdtWalletAddress
        ));
        $transfer = new TransferOptions((int)$wallet->seqno($transport));
        $extMessage = $wallet->createTransferMessage([
            new Transfer(
                $usdtWalletAddress,
                Units::toNano("0.1"),
                $usdtWallet->createTransferBody(
                    new TransferJettonOptions(
                        Units::toNano($transferAmount, Units::USDt),
                        new Address($destAddress),
                        $walletAddress,
                        0,
                        SnakeString::fromString($toMemo)->cell(true),
                        Units::toNano("0.0000001")
                    )
                ),
                SendMode::combine([SendMode::PAY_GAS_SEPARATELY, SendMode::CARRY_ALL_REMAINING_INCOMING_VALUE,
                    SendMode::IGNORE_ERRORS])
            )],
            $transfer
        );
        $tonResponse = $transport->sendMessageReturnHash($extMessage, $kp->secretKey);
        InsertWithdrawTonTransaction::dispatch($tonResponse, $fromMemo, $destAddress, (float)$transferAmount,
            TransactionHelper::USDT, $toMemo);
    }
}
