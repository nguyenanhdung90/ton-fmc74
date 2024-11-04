<?php

namespace App\TON\Withdraws;

use App\TON\Contracts\Wallets\Exceptions\WalletException;
use App\TON\Contracts\Wallets\Transfer;
use App\TON\Contracts\Wallets\TransferOptions;
use App\TON\Contracts\Wallets\V4\WalletV4R2;
use App\TON\Exceptions\TransportException;
use App\TON\Exceptions\WithdrawTonException;
use App\TON\Interop\Address;
use App\TON\Interop\Units;
use App\TON\Mnemonic\Exceptions\TonMnemonicException;
use App\TON\Mnemonic\TonMnemonic;
use App\TON\SendMode;
use App\TON\Transactions\TransactionHelper;

abstract class WithdrawTonAbstract extends WithdrawAbstract
{
    /**
     * @throws WalletException
     * @throws TonMnemonicException
     * @throws TransportException
     * @throws WithdrawTonException
     */
    public function process(string $fromMemo, string $toAddress,
                            float $transferAmount, string $toMemo = "", bool $isAllRemainBalance = false)
    {
        $wallet = $this->validGetWalletMemo($fromMemo, TransactionHelper::TON);
        $transactionId = $this->syncToWalletGetIdTransaction(
            $fromMemo,
            $toAddress,
            (string)Units::toNano($transferAmount),
            TransactionHelper::TON,
            Units::DEFAULT,
            $toMemo,
            null,
            $isAllRemainBalance
        );
        if ($isAllRemainBalance) {
            $transferNano = $wallet->amount - TransactionHelper::getFixedFeeByCurrency(TransactionHelper::TON);
            $transferDecimal = (string)Units::fromNano($transferNano);
            $transferUnit = Units::toNano($transferDecimal);
        } else {
            $transferUnit = Units::toNano($transferAmount);
        }
        if (!$transactionId) {
            throw new WithdrawTonException("There is error when sync transaction Ton to wallet");
        }
        $phrases = config('services.ton.ton_mnemonic');
        $transport = $this->getTransport();
        $kp = TonMnemonic::mnemonicToKeyPair(explode(" ", $phrases));
        /** @var WalletV4R2 $wallet */
        $wallet = $this->getWallet($kp->publicKey);
        $extMsg = $wallet->createTransferMessage(
            [
                new Transfer(
                    new Address($toAddress),
                    $transferUnit,
                    $toMemo,
                    SendMode::PAY_GAS_SEPARATELY
                )
            ],
            new TransferOptions((int)$wallet->seqno($transport))
        );
        $responseMessage = $transport->sendMessageReturnHash($extMsg, $kp->secretKey);
        $this->syncBy($responseMessage, $transactionId);
    }
}


