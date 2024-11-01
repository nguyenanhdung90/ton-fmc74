<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

use App\Models\WalletTonTransaction;

abstract class TransactionUpdateToWalletAbstract
{
    protected WalletTonTransaction $transaction;

    public function __construct(WalletTonTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    abstract public function getUpdateTransactionToWallet(): UpdateAmountFeeTransactionInterface;

    public function updateToAmountWallet()
    {
        $updateTransactionToWallet = $this->getUpdateTransactionToWallet();
        $updateTransactionToWallet->process();
    }
}
