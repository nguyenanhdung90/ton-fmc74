<?php

namespace App\TON\Withdraws;

use App\Jobs\InsertTonWithdrawTransaction;
use App\Models\WalletTonMemo;
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
    public function process(string $fromMemo, string $toAddress, float $transferAmount, string $toMemo = "")
    {
        $this->isValidWalletTransferAmount($fromMemo, $transferAmount, TransactionHelper::TON);
        $phrases = config('services.ton.ton_mnemonic');
        $transport = $this->getTransport();
        $kp = TonMnemonic::mnemonicToKeyPair(explode(" ", $phrases));
        /** @var WalletV4R2 $wallet */
        $wallet = $this->getWallet($kp->publicKey);
        $extMsg = $wallet->createTransferMessage(
            [
                new Transfer(
                    new Address($toAddress),
                    Units::toNano($transferAmount),
                    $toMemo,
                    SendMode::combine([SendMode::CARRY_ALL_REMAINING_INCOMING_VALUE, SendMode::IGNORE_ERRORS])
                )
            ],
            new TransferOptions((int)$wallet->seqno($transport))
        );
        $tonResponse = $transport->sendMessageReturnHash($extMsg, $kp->secretKey);

        InsertTonWithdrawTransaction::dispatch(
            $tonResponse,
            $fromMemo,
            $toAddress,
            (string)Units::toNano($transferAmount),
            TransactionHelper::TON,
            Units::DEFAULT,
            $toMemo
        );
    }
}


