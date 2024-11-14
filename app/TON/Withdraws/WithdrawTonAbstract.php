<?php

namespace App\TON\Withdraws;

use App\Models\WalletTonTransaction;
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
use App\TON\Transactions\SyncTransactionToWallet\TransactionWithdrawSyncFixedFee;
use App\TON\TonHelper;

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
        $transactionId = $this->syncToWalletGetIdTransaction(
            $fromMemo,
            $toAddress,
            (string)Units::toNano($transferAmount),
            TonHelper::TON,
            $toMemo,
            null,
            $isAllRemainBalance
        );
        if (!$transactionId) {
            throw new WithdrawTonException("There is error when sync transaction Ton to wallet");
        }

        $transaction = WalletTonTransaction::find($transactionId);
        $transferAmount = (string)Units::fromNano($transaction->amount);
        $transferUnit = Units::toNano($transferAmount);

        $transactionWithdraw = new TransactionWithdrawSyncFixedFee($transactionId);
        $transactionWithdraw->syncTransactionWallet();

        $phrases = config('services.ton.mnemonic');
        $transport = TonHelper::getTransport();
        $kp = TonMnemonic::mnemonicToKeyPair(explode(" ", $phrases));
        /** @var WalletV4R2 $wallet */
        $wallet = $this->getWallet($kp->publicKey);
        $extMsg = $wallet->createTransferMessage(
            [
                new Transfer(
                    new Address($toAddress),
                    $transferUnit,
                    $toMemo,
                    SendMode::combine([SendMode::PAY_GAS_SEPARATELY, SendMode::IGNORE_ERRORS])
                )
            ],
            new TransferOptions((int)$wallet->seqno($transport))
        );
        $responseMessage = $transport->sendMessageReturnHash($extMsg, $kp->secretKey);
        $this->syncProcessingOrFailedBy($responseMessage, $transactionId);
    }
}


