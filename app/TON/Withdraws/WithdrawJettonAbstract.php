<?php

namespace App\TON\Withdraws;

use App\Models\WalletTonTransaction;
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
use App\TON\TonHelper;
use App\TON\Transactions\SyncTransactionToWallet\TransactionWithdrawSyncFixedFee;

abstract class WithdrawJettonAbstract extends WithdrawAbstract
{
    abstract public function getCurrency(): string;

    abstract public function getDecimals(): int;

    abstract public function getMasterJettonAddress(): string;

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
        $queryId = hexdec(uniqid());
        $currency = $this->getCurrency();
        $decimals = $this->getDecimals();
        $jettonMasterAddress = $this->getMasterJettonAddress();
        $transactionId = $this->syncToWalletGetIdTransaction(
            $fromMemo,
            $destAddress,
            (string)Units::toNano($transferAmount, $decimals),
            $currency,
            $decimals,
            $toMemo,
            $queryId,
            $isAllRemainBalance
        );
        if (!$transactionId) {
            throw new WithdrawTonException("There is error when sync transaction jetton to wallet");
        }

        $transaction = WalletTonTransaction::find($transactionId);
        $transferAmount = (string)Units::fromNano($transaction->amount, $decimals);
        $transferUnit = Units::toNano($transferAmount, $decimals);

        $transactionWithdraw = new TransactionWithdrawSyncFixedFee($transactionId);
        $transactionWithdraw->syncTransactionWallet();

        $phrases = config('services.ton.mnemonic');
        $kp = TonMnemonic::mnemonicToKeyPair(explode(" ", $phrases));
        $wallet = $this->getWallet($kp->publicKey);
        /** @var Address $walletAddress */
        $walletAddress = $wallet->getAddress();
        $transport = TonHelper::getTransport();
        $jettonRoot = JettonMinter::fromAddress(
            $transport,
            new Address($jettonMasterAddress)
        );
        $jettonWalletAddress = $jettonRoot->getJettonWalletAddress($transport, $walletAddress);
        $jettonWallet = new JettonWallet(new JettonWalletOptions(
            null, 0, $jettonWalletAddress
        ));
        $transfer = new TransferOptions((int)$wallet->seqno($transport));
        $extMessage = $wallet->createTransferMessage([
            new Transfer(
                $jettonWalletAddress,
                Units::toNano("0.1"),
                $jettonWallet->createTransferBody(
                    new TransferJettonOptions(
                        $transferUnit,
                        new Address($destAddress),
                        $walletAddress,
                        $queryId,
                        SnakeString::fromString($toMemo)->cell(true),
                        Units::toNano("0.000000001")
                    )
                ),
                SendMode::combine([SendMode::CARRY_ALL_REMAINING_INCOMING_VALUE, SendMode::IGNORE_ERRORS])
            )],
            $transfer
        );
        $responseMessage = $transport->sendMessageReturnHash($extMessage, $kp->secretKey);
        $this->syncProcessingOrFailedBy($responseMessage, $transactionId);
    }
}
