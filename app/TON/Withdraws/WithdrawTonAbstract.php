<?php

namespace App\TON\Withdraws;

use App\Jobs\TonInsertWithdrawTransaction;
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
        $walletMemo = $this->validGetWalletMemo($fromMemo, $transferAmount, TransactionHelper::TON);
        $phrases = config('services.ton.ton_mnemonic');
        $transport = $this->getTransport();
        $kp = TonMnemonic::mnemonicToKeyPair(explode(" ", $phrases));
        /** @var WalletV4R2 $wallet */
        $wallet = $this->getWallet($kp->publicKey);
        $sendMode = $isAllRemainBalance ?
            SendMode::combine([SendMode::CARRY_ALL_REMAINING_INCOMING_VALUE, SendMode::IGNORE_ERRORS]) :
            SendMode::PAY_GAS_SEPARATELY;
        $amountWalletDecimal = (string)Units::fromNano($walletMemo->amount, $walletMemo->decimals);
        $transferUnit = $isAllRemainBalance ? Units::toNano($amountWalletDecimal) : Units::toNano($transferAmount);
        $extMsg = $wallet->createTransferMessage(
            [
                new Transfer(
                    new Address($toAddress),
                    $transferUnit,
                    $toMemo,
                    $sendMode
                )
            ],
            new TransferOptions((int)$wallet->seqno($transport))
        );
        $tonResponse = $transport->sendMessageReturnHash($extMsg, $kp->secretKey);

        TonInsertWithdrawTransaction::dispatch(
            $tonResponse,
            $fromMemo,
            $toAddress,
            (string)$transferUnit,
            TransactionHelper::TON,
            Units::DEFAULT,
            $toMemo,
            null,
            $isAllRemainBalance
        );
    }
}


