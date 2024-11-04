<?php

namespace App\TON\Withdraws;

use App\TON\Contracts\Exceptions\ContractException;
use App\TON\Contracts\Jetton\JettonMinter;
use App\TON\Contracts\Jetton\JettonWallet;
use App\TON\Contracts\Jetton\JettonWalletOptions;
use App\TON\Contracts\Jetton\TransferJettonOptions;
use App\TON\Contracts\Wallets\Transfer;
use App\TON\Contracts\Wallets\TransferOptions;
use App\TON\Exceptions\TransportException;
use App\TON\Exceptions\WithdrawTonException;
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
     * @throws WithdrawTonException
     */
    public function process(string $fromMemo, string $destAddress, string $transferAmount, string $toMemo = "",
                            bool $isAllRemainBalance = false)
    {
        $wallet = $this->validGetWalletMemo($fromMemo, TransactionHelper::USDT);
        $queryId = hexdec(uniqid());
        $transactionId = $this->syncToWalletGetIdTransaction(
            $fromMemo,
            $destAddress,
            (string)Units::toNano($transferAmount, Units::USDt),
            TransactionHelper::USDT,
            Units::USDt,
            $toMemo,
            $queryId,
            $isAllRemainBalance
        );
        if (!$transactionId) {
            throw new WithdrawTonException("There is error when sync transaction Ton to wallet");
        }

        if ($isAllRemainBalance) {
            $transferNano = $wallet->amount - TransactionHelper::getFixedFeeByCurrency(TransactionHelper::USDT);
            $transferDecimal = (string)Units::fromNano($transferNano, Units::USDt);
            $transferUnit = Units::toNano($transferDecimal, Units::USDt);
        } else {
            $transferUnit = Units::toNano($transferAmount, Units::USDt);
        }
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
                        $transferUnit,
                        new Address($destAddress),
                        $walletAddress,
                        $queryId,
                        SnakeString::fromString($toMemo)->cell(true),
                        Units::toNano("0.0000001")
                    )
                ),
                SendMode::combine([SendMode::CARRY_ALL_REMAINING_INCOMING_VALUE, SendMode::IGNORE_ERRORS])
            )],
            $transfer
        );
        $responseMessage = $transport->sendMessageReturnHash($extMessage, $kp->secretKey);
        $this->syncBy($responseMessage, $transactionId);
    }
}
